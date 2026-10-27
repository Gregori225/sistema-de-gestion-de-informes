<?php
session_start();

// SEGURIDAD: Verificar sesión activa
if (empty($_SESSION["id"])) {
    header("location: login.php");
    exit();
}

require_once("config/config.php");
require_once("acciones/obtenerInformes.php");

$user_id = $_SESSION["id"];
$rol = $_SESSION["rol"] ?? 'invitado';

try {
    // 1. Métricas clave (KPIs)
    $total_pendientes = obtenerInformesPendientes($conexion, $user_id);

    $sql_enviados = "SELECT COUNT(*) FROM public.informes_tecnicos WHERE id_tecnico = :user_id";
    $stmt_env = $conexion->prepare($sql_enviados);
    $stmt_env->execute([':user_id' => $user_id]);
    $total_enviados = (int)$stmt_env->fetchColumn();

    $sql_recibidos = "SELECT COUNT(*) FROM public.informes_tecnicos WHERE id_solicitante = :user_id";
    $stmt_rec = $conexion->prepare($sql_recibidos);
    $stmt_rec->execute([':user_id' => $user_id]);
    $total_recibidos = (int)$stmt_rec->fetchColumn();

    // 2. Consulta para el gráfico de estados (solo admin)
    $totales_por_estado = [
        'Pendiente'  => 0,
        'Leido' => 0,
        'En Proceso' => 0,
        'Resuelto' => 0,
        'Cancelado' => 0
    ];

    if ($rol === 'admin') {
        $sql_grafico = "SELECT estado, COUNT(*) as total FROM public.informes_tecnicos GROUP BY estado";
        $stmt_grafico = $conexion->query($sql_grafico);
        $resultados = $stmt_grafico->fetchAll(PDO::FETCH_ASSOC);
        foreach ($resultados as $row) {
            if (array_key_exists($row['estado'], $totales_por_estado)) {
                $totales_por_estado[$row['estado']] = (int)$row['total'];
            }
        }
    }

    // 3. OBTENER CONTEO DE NOTIFICACIONES NO LEÍDAS
    $sql_notif = "SELECT COUNT(*) FROM public.notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
    $stmt_notif = $conexion->prepare($sql_notif);
    $stmt_notif->execute([':user_id' => $user_id]);
    $no_leidas = (int)$stmt_notif->fetchColumn();
} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $total_pendientes = 0;
    $total_enviados = 0;
    $total_recibidos = 0;
    $no_leidas = 0;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Fundasalud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">

    <?php if ($rol === 'admin'): ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php endif; ?>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- MENÚ LATERAL -->
            <?php include("includes/sidebar.php"); ?>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-md-9 col-lg-10 main-content p-4">

                <!-- ENCABEZADO -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold mb-1">Bienvenido, <?= htmlspecialchars($_SESSION["nombre"] ?? ''); ?></h2>
                        <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION["cargo"] ?? 'Coordinación de Informática'); ?></p>
                    </div>

                    <div class="d-flex align-items-center gap-2">

                        <!-- 🔔 SISTEMA DE NOTIFICACIONES INTEGRADO -->
                        <div class="position-relative d-inline-block">
                            <button id="btn-notificaciones" class="btn btn-light border position-relative" style="width: 45px; height: 45px; border-radius: 50%;">
                                <i class="fa-solid fa-bell text-secondary"></i>
                                <?php if ($no_leidas > 0): ?>
                                    <span id="badge-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        <?= $no_leidas ?>
                                    </span>
                                <?php else: ?>
                                    <span id="badge-contador" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
                                <?php endif; ?>
                            </button>

                            <!-- Dropdown de notificaciones -->
                            <div id="dropdown-notificaciones" class="d-none position-absolute end-0 mt-2 bg-white border rounded shadow" style="width: 350px; max-height: 400px; overflow-y: auto; z-index: 1050;">
                                <div class="p-3 border-bottom bg-light rounded-top">
                                    <strong class="text-secondary"><i class="fa-solid fa-bell me-2"></i>Notificaciones Recientes</strong>
                                </div>
                                <div id="lista-notificaciones" class="p-2">
                                    <p class="text-center text-muted py-3 small"><i class="fa-solid fa-circle-notch fa-spin me-1"></i> Cargando...</p>
                                </div>
                                <div class="p-2 border-top text-center bg-light rounded-bottom">
                                    <a href="buzon.php" class="text-decoration-none small fw-bold text-primary">
                                        Ir al Buzón <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- FIN NOTIFICACIONES -->

                        <a href="informes.php" class="btn btn-primary shadow-sm">
                            <i class="fa-solid fa-plus me-1"></i> Nuevo Informe
                        </a>
                    </div>
                </div>

                <!-- TARJETAS DE MÉTRICAS (KPIs) -->
                <div class="row g-4 mb-4">
                    <!-- Informes Sin Leer -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-warning border-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold">Por Leer</span>
                                    <h2 class="fw-bold my-1 text-warning"><?= $total_pendientes ?></h2>
                                    <small class="text-muted">Pendientes en tu buzón</small>
                                </div>
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle">
                                    <i class="fa-solid fa-envelope-open-text fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Creados / Enviados -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-primary border-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold">Mis Informes</span>
                                    <h2 class="fw-bold my-1 text-primary"><?= $total_enviados ?></h2>
                                    <small class="text-muted">Redactados y enviados</small>
                                </div>
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle">
                                    <i class="fa-solid fa-paper-plane fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Recibidos -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm p-3 bg-white h-100 border-start border-success border-4">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="text-muted small text-uppercase fw-bold">Total Recibidos</span>
                                    <h2 class="fw-bold my-1 text-success"><?= $total_recibidos ?></h2>
                                    <small class="text-muted">Histórico en tu buzón</small>
                                </div>
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle">
                                    <i class="fa-solid fa-inbox fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN DE GRÁFICOS (SOLO VISIBLE PARA ADMIN) -->
                <?php if ($rol === 'admin'): ?>
                    <div class="row g-4">
                        <!-- GRÁFICO 1: Estado General -->
                        <div class="col-md-6 col-lg-5 mb-4">
                            <div class="card shadow-sm border-0 rounded-3 h-100">
                                <div class="card-header bg-white py-3 border-0">
                                    <h5 class="card-title mb-0 fw-bold text-secondary">
                                        <i class="fa-solid fa-chart-pie me-2 text-primary"></i> Estado de los Informes
                                    </h5>
                                </div>
                                <div class="card-body d-flex align-items-center justify-content-center p-3">
                                    <div style="width: 100%; max-width: 300px;">
                                        <canvas id="graficoEstadosInformes"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- GRÁFICO 2: Informes Resueltos -->
                        <div class="col-md-6 col-lg-7 mb-4">
                            <div class="card shadow-sm border-0 rounded-3 h-100">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold text-secondary">
                                        <i class="fa-solid fa-chart-line me-2 text-success"></i> Informes Resueltos
                                    </h5>
                                    <select id="filtroResueltos" class="form-select form-select-sm w-auto">
                                        <option value="semana" selected>Últimos 7 días</option>
                                        <option value="meses">Últimos 6 meses</option>
                                    </select>
                                </div>
                                <div class="card-body p-3">
                                    <canvas id="graficoResueltos" style="max-height: 280px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- SCRIPTS DE CONFIGURACIÓN DE LOS GRÁFICOS Y NOTIFICACIONES -->
    <?php if ($rol === 'admin'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // ... (Tu código existente de Chart.js va aquí exactamente igual) ...
                const ctxEstados = document.getElementById('graficoEstadosInformes').getContext('2d');
                new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendiente', 'Leído', 'En Proceso', 'Resuelto', 'Cancelado'],
                        datasets: [{
                            data: [<?= $totales_por_estado['Pendiente'] ?>, <?= $totales_por_estado['Leido'] ?>, <?= $totales_por_estado['En Proceso'] ?>, <?= $totales_por_estado['Resuelto'] ?>, <?= $totales_por_estado['Cancelado'] ?>],
                            backgroundColor: ['#ffc107', '#0dcaf0', '#0d6efd', '#198754', '#dc3545'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            }
                        }
                    }
                });

                const ctxResueltos = document.getElementById('graficoResueltos').getContext('2d');
                let graficoResueltosInstance = null;

                function cargarGraficoResueltos(filtro) {
                    fetch(`acciones/obtener_grafico_resueltos.php?filtro=${filtro}`)
                        .then(res => res.json()).then(data => {
                            if (data.status === 'ok') {
                                if (graficoResueltosInstance) graficoResueltosInstance.destroy();
                                graficoResueltosInstance = new Chart(ctxResueltos, {
                                    type: 'line',
                                    data: {
                                        labels: data.labels.length ? data.labels : ['Sin datos'],
                                        datasets: [{
                                            label: 'Informes Resueltos',
                                            data: data.totals.length ? data.totals : [0],
                                            borderColor: '#198754',
                                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                            borderWidth: 3,
                                            tension: 0.3,
                                            fill: true,
                                            pointBackgroundColor: '#198754',
                                            pointRadius: 4,
                                            pointHoverRadius: 6
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                ticks: {
                                                    stepSize: 1
                                                }
                                            }
                                        },
                                        plugins: {
                                            legend: {
                                                display: false
                                            }
                                        }
                                    }
                                });
                            }
                        }).catch(err => console.error("Error al cargar gráfico de resueltos:", err));
                }
                cargarGraficoResueltos('semana');
                document.getElementById('filtroResueltos').addEventListener('change', function() {
                    cargarGraficoResueltos(this.value);
                });
            });
        </script>
    <?php endif; ?>

    <!-- SCRIPT DE NOTIFICACIONES -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const btnNotif = document.getElementById('btn-notificaciones');
            const dropdown = document.getElementById('dropdown-notificaciones');
            const listaNotif = document.getElementById('lista-notificaciones');
            const badge = document.getElementById('badge-contador');

            if (!btnNotif) return;

            // 1. Mostrar/Ocultar dropdown
            btnNotif.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = !dropdown.classList.contains('d-none');
                dropdown.classList.toggle('d-none', isVisible);
                if (!isVisible) cargarNotificaciones();
            });

            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !btnNotif.contains(e.target)) {
                    dropdown.classList.add('d-none');
                }
            });

            // 2. Cargar notificaciones vía AJAX
            function cargarNotificaciones() {
                fetch('acciones/obtener_notificaciones.php')
                    .then(res => res.json())
                    .then(data => {
                        if (data.notificaciones.length === 0) {
                            listaNotif.innerHTML = '<p class="text-center text-muted py-3 small">No tienes notificaciones nuevas</p>';
                            return;
                        }
                        let html = '';
                        data.notificaciones.forEach(n => {
                            const estilo = n.leida ? 'opacity: 0.7;' : 'background-color: #f8f9fa; border-left: 3px solid #0d6efd;';
                            const colorTipo = n.tipo === 'success' ? '#198754' : (n.tipo === 'error' ? '#dc3545' : '#0dcaf0');
                            html += `
                                <div class="p-2 mb-2 rounded cursor-pointer" style="${estilo}" onclick="marcarLeida(${n.id})">
                                    <div class="fw-bold small" style="color: ${colorTipo};">${n.titulo}</div>
                                    <div class="small text-dark mt-1">${n.mensaje}</div>
                                    <div class="small text-muted mt-1"><i class="fa-regular fa-clock me-1"></i>${formatearFecha(n.fecha_creacion)}</div>
                                </div>
                            `;
                        });
                        listaNotif.innerHTML = html;
                    })
                    .catch(err => {
                        listaNotif.innerHTML = '<p class="text-center text-danger py-3 small">Error al cargar</p>';
                    });
            }

            // 3. Marcar como leída
            window.marcarLeida = function(id) {
                const formData = new FormData();
                formData.append('id', id);
                fetch('acciones/marcar_notificacion_leida.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            let conteo = parseInt(badge.innerText);
                            if (conteo > 1) {
                                badge.innerText = conteo - 1;
                            } else {
                                badge.classList.add('d-none');
                            }
                            cargarNotificaciones(); // Refrescar lista
                        }
                    });
            };

            // 4. Formatear fecha relativa
            function formatearFecha(fechaStr) {
                const diff = Date.now() - new Date(fechaStr).getTime();
                const mins = Math.floor(diff / 60000);
                if (mins < 1) return 'Justo ahora';
                if (mins < 60) return `Hace ${mins} min`;
                const horas = Math.floor(diff / 3600000);
                if (horas < 24) return `Hace ${horas} h`;
                return `Hace ${Math.floor(diff / 86400000)} días`;
            }

            // 5. Auto-refresh del contador cada 60 segundos
            setInterval(() => {
                fetch('acciones/obtener_notificaciones.php?solo_contador=1')
                    .then(res => res.json())
                    .then(data => {
                        badge.innerText = data.no_leidas;
                        if (data.no_leidas > 0) badge.classList.remove('d-none');
                        else badge.classList.add('d-none');
                    });
            }, 60000);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>