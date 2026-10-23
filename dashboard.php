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
    // Informes recibidos en estado Pendiente
    $total_pendientes = obtenerInformesPendientes($conexion, $user_id);

    // Total de informes creados/enviados por este usuario
    $sql_enviados = "SELECT COUNT(*) FROM public.informes_tecnicos WHERE id_tecnico = :user_id";
    $stmt_env = $conexion->prepare($sql_enviados);
    $stmt_env->execute([':user_id' => $user_id]);
    $total_enviados = (int)$stmt_env->fetchColumn();

    // Total de informes recibidos por este usuario
    $sql_recibidos = "SELECT COUNT(*) FROM public.informes_tecnicos WHERE id_solicitante = :user_id";
    $stmt_rec = $conexion->prepare($sql_recibidos);
    $stmt_rec->execute([':user_id' => $user_id]);
    $total_recibidos = (int)$stmt_rec->fetchColumn();

    // 2. Consulta para alimentar el gráfico de estados solo si el usuario es admin
    $totales_por_estado = [
        'Pendiente'  => 0,
        'Leido'      => 0,
        'En Proceso' => 0,
        'Resuelto'   => 0,
        'Cancelado'  => 0
    ];

    if ($rol === 'admin') {
        $sql_grafico = "SELECT estado, COUNT(*) as total 
                        FROM public.informes_tecnicos 
                        GROUP BY estado";
        $stmt_grafico = $conexion->query($sql_grafico);
        $resultados = $stmt_grafico->fetchAll(PDO::FETCH_ASSOC);

        foreach ($resultados as $row) {
            if (array_key_exists($row['estado'], $totales_por_estado)) {
                $totales_por_estado[$row['estado']] = (int)$row['total'];
            }
        }
    }
} catch (PDOException $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $total_pendientes = 0;
    $total_enviados = 0;
    $total_recibidos = 0;
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

    <!-- Cargar Chart.js solo si es admin para ahorrar recursos -->
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold mb-1">Bienvenido, <?= htmlspecialchars($_SESSION["nombre"] ?? ''); ?></h2>
                        <p class="text-muted mb-0"><?= htmlspecialchars($_SESSION["cargo"] ?? 'Coordinación de Informática'); ?></p>
                    </div>
                    <div>
                        <a href="informes.php" class="btn btn-primary shadow-sm me-2">
                            <i class="fa-solid fa-plus me-1"></i> Nuevo Informe
                        </a>
                        <a href="buzon.php" class="btn btn-outline-secondary shadow-sm">
                            <i class="fa-solid fa-inbox me-1"></i> Ir al Buzón
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

                        <!-- GRÁFICO 1: Estado General de los Informes (Doughnut) -->
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

                        <!-- GRÁFICO 2: Informes Resueltos con Filtro (Line Chart) -->
                        <div class="col-md-6 col-lg-7 mb-4">
                            <div class="card shadow-sm border-0 rounded-3 h-100">
                                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                                    <h5 class="card-title mb-0 fw-bold text-secondary">
                                        <i class="fa-solid fa-chart-line me-2 text-success"></i> Informes Resueltos
                                    </h5>
                                    <!-- Selector de filtro (semana / meses) -->
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

    <!-- SCRIPTS DE CONFIGURACIÓN DE LOS GRÁFICOS (SOLO SE EJECUTAN SI ES ADMIN) -->
    <?php if ($rol === 'admin'): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                // -------------------------------------------------------------
                // GRÁFICO 1: DOUGHNUT (Estados de Informes)
                // -------------------------------------------------------------
                const ctxEstados = document.getElementById('graficoEstadosInformes').getContext('2d');

                new Chart(ctxEstados, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pendiente', 'Leído', 'En Proceso', 'Resuelto', 'Cancelado'],
                        datasets: [{
                            data: [
                                <?= $totales_por_estado['Pendiente'] ?>,
                                <?= $totales_por_estado['Leido'] ?>,
                                <?= $totales_por_estado['En Proceso'] ?>,
                                <?= $totales_por_estado['Resuelto'] ?>,
                                <?= $totales_por_estado['Cancelado'] ?>
                            ],
                            backgroundColor: [
                                '#ffc107', // Pendiente: Amarillo
                                '#0dcaf0', // Leído: Azul claro
                                '#0d6efd', // En Proceso: Azul oscuro
                                '#198754', // Resuelto: Verde
                                '#dc3545' // Cancelado: Rojo
                            ],
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
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const porcentaje = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return ` ${label}: ${value} (${porcentaje}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                // -------------------------------------------------------------
                // GRÁFICO 2: LÍNEAS DINÁMICAS (Informes Resueltos)
                // -------------------------------------------------------------
                const ctxResueltos = document.getElementById('graficoResueltos').getContext('2d');
                let graficoResueltosInstance = null;

                function cargarGraficoResueltos(filtro) {
                    fetch(`acciones/obtener_grafico_resueltos.php?filtro=${filtro}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'ok') {
                                if (graficoResueltosInstance) {
                                    graficoResueltosInstance.destroy();
                                }

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
                        })
                        .catch(err => console.error("Error al cargar gráfico de resueltos:", err));
                }

                // Cargar por defecto (Últimos 7 días)
                cargarGraficoResueltos('semana');

                // Escuchar cambios en el selector
                document.getElementById('filtroResueltos').addEventListener('change', function() {
                    cargarGraficoResueltos(this.value);
                });
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>