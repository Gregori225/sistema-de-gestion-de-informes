<?php
session_start();

// SEGURIDAD: Verificar sesión activa
if (empty($_SESSION["id"])) {
    header("location: login.php");
    exit();
}

require_once("config/config.php");
include("acciones/acciones.php");

// Formato de fecha en español
$meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$fecha_actual = "Trujillo, " . date('d') . " de " . $meses[date('n') - 1] . " de " . date('Y');

// CONSULTA A POSTGRESQL (PDO): Cargar usuarios activos
$sql_usuarios = "SELECT u.id, u.nombre, u.cargo, d.nombre AS departamento 
                 FROM public.usuarios u 
                 LEFT JOIN public.departamentos d ON u.id_departamento = d.id 
                 WHERE u.activo = true 
                 ORDER BY u.nombre ASC";

try {
    $stmt = $conexion->query($sql_usuarios);
    $lista_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $lista_usuarios = [];
    error_log("Error al consultar usuarios: " . $e->getMessage());
}

// Obtener los datos del usuario en sesión actual
$usuario_sesion = null;
foreach ($lista_usuarios as $u) {
    if ($u['id'] == $_SESSION["id"]) {
        $usuario_sesion = $u;
        break;
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generador de Informe Técnico - Fundasalud</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Estilos Propios -->
    <link rel="stylesheet" href="./css/home.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/informes.css">
</head>

<body>
    <div class="d-flex min-vh-100">

        <!-- 🗂️ MENÚ LATERAL (SIDEBAR) -->
        <?php include("includes/sidebar.php"); ?>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="col-md-9 col-lg-10 main-content main-content-col p-4">

            <!-- FORMULARIO PRINCIPAL -->
            <div class="card shadow-sm border-0 p-4 form-container">
                <h2 class="h3 mb-4 text-primary"><i class="fa-solid fa-paper-plane me-2"></i>Enviar Informe Técnico</h2>

                <!-- Sección 1: Información Básica -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 text-secondary">Información Básica</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="in-num" class="form-label font-weight-bold">Número Correlativo</label>
                            <input type="text" id="in-num" value="Autogenerado en BD" readonly class="form-control bg-light">
                        </div>
                        <div class="col-md-6">
                            <label for="in-fecha" class="form-label">Lugar y Fecha</label>
                            <input type="text" id="in-fecha" class="form-control" value="<?= htmlspecialchars($fecha_actual) ?>">
                        </div>
                    </div>
                </div>

                <!-- Sección 2: Personal Interviniente -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 text-secondary">Personal Interviniente</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="select-para" class="form-label">Seleccionar Destinatario (PARA)</label>
                            <select id="select-para" class="form-select">
                                <option value="">-- Seleccionar de la BD --</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="in-firmante" class="form-label">Nombre Remitente / Firmante (DE)</label>
                            <input type="text" id="in-firmante" class="form-control bg-light" value="<?= htmlspecialchars(mb_strtoupper($usuario_sesion['nombre'] ?? '')) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="in-cargo" class="form-label">Cargo Firmante</label>
                            <input type="text" id="in-cargo" class="form-control bg-light" value="<?= htmlspecialchars($usuario_sesion['cargo'] ?? '') ?>" readonly>
                        </div>
                    </div>
                </div>

                <!-- Sección 3: Detalle del Dispositivo -->
                <div class="mb-4">
                    <h5 class="border-bottom pb-2 text-secondary">Detalle del Dispositivo</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="in-desc" class="form-label">Descripción</label>
                            <textarea id="in-desc" class="form-control" rows="3" placeholder="Ingrese la descripción del equipo..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="in-diag" class="form-label">Diagnóstico / Problema</label>
                            <textarea id="in-diag" class="form-control" rows="3" placeholder="Ingrese el diagnóstico o problema detectado..."></textarea>
                        </div>
                        <div class="col-12">
                            <label for="in-obs" class="form-label">Observación</label>
                            <textarea id="in-obs" class="form-control" rows="3" placeholder="Ingrese las observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Botón de Envío -->
                <div class="text-end">
                    <button type="button" id="btn-enviar" class="btn btn-primary btn-lg">
                        <i class="fa-solid fa-paper-plane me-2"></i> <span id="btn-text">Enviar Informe</span>
                    </button>
                </div>
            </div>

        </main>
    </div>

    <!-- Scripts Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Lógica JavaScript con SweetAlert2 -->
    <script>
        const personalBD = <?= json_encode($lista_usuarios, JSON_UNESCAPED_UNICODE) ?>;

        document.addEventListener('DOMContentLoaded', () => {
            poblarSelects();

            // Listener del botón de envío
            document.getElementById('btn-enviar').addEventListener('click', enviarInforme);
        });

        function poblarSelects() {
            const selectPara = document.getElementById('select-para');

            personalBD.forEach(persona => {
                const text = `${persona.nombre} (${persona.cargo})`;
                selectPara.add(new Option(text, persona.id));
            });
        }

        async function enviarInforme() {
            const idSolicitante = document.getElementById('select-para').value;
            const descEquipo = document.getElementById('in-desc').value.trim();
            const diagProblema = document.getElementById('in-diag').value.trim();
            const observacion = document.getElementById('in-obs').value.trim();

            // Validaciones con SweetAlert2
            if (!idSolicitante) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Por favor seleccione un Destinatario (PARA).',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            if (!descEquipo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo requerido',
                    text: 'Por favor complete la Descripción del Equipo.',
                    confirmButtonColor: '#0d6efd'
                });
                return;
            }

            const btn = document.getElementById('btn-enviar');
            const btnText = document.getElementById('btn-text');

            // Feedback visual al usuario
            btn.disabled = true;
            btnText.innerText = 'Enviando...';

            const datos = {
                id_solicitante: idSolicitante,
                descripcion_equipo: descEquipo,
                diagnostico_problema: diagProblema,
                observacion: observacion
            };

            try {
                const response = await fetch('guardar_informe.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const res = await response.json();

                if (res.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Enviado!',
                        text: 'El informe se ha enviado exitosamente.',
                        confirmButtonColor: '#198754'
                    }).then(() => {
                        // Limpiar campos para un nuevo informe
                        document.getElementById('in-desc').value = '';
                        document.getElementById('in-diag').value = '';
                        document.getElementById('in-obs').value = '';
                        document.getElementById('select-para').value = '';
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo procesar el envío: ' + res.message,
                        confirmButtonColor: '#dc3545'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'Ocurrió un error al intentar conectar con el servidor.',
                    confirmButtonColor: '#dc3545'
                });
            } finally {
                // Restablecer botón
                btn.disabled = false;
                btnText.innerText = 'Enviar Informe';
            }
        }
    </script>
</body>

</html>