<?php
session_start();

if (empty($_SESSION["id"]) || empty($_GET["id"])) {
    header("location: buzon.php");
    exit();
}

require_once("config/config.php");

$id_informe = intval($_GET["id"]);
$user_id = $_SESSION["id"];
$rol = $_SESSION["rol"] ?? 'invitado';

try {
    $sql = "SELECT i.*, 
                   u_dest.nombre AS para_nombre, u_dest.cargo AS para_cargo, d_dest.nombre AS para_depto,
                   u_rem.nombre AS de_nombre, u_rem.cargo AS de_cargo, d_rem.nombre AS de_depto
            FROM public.informes_tecnicos i
            LEFT JOIN public.usuarios u_dest ON i.id_solicitante = u_dest.id
            LEFT JOIN public.departamentos d_dest ON u_dest.id_departamento = d_dest.id
            LEFT JOIN public.usuarios u_rem ON i.id_tecnico = u_rem.id
            LEFT JOIN public.departamentos d_rem ON u_rem.id_departamento = d_rem.id
            WHERE i.id = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id_informe]);
    $informe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$informe) {
        die("<div class='container mt-5 alert alert-danger'>El informe solicitado no existe.</div>");
    }

    // SEGURIDAD: solo los participantes del informe (solicitante o técnico)
    // pueden verlo; los administradores tienen acceso total.
    $es_participante = ($rol === 'admin')
        || intval($informe['id_solicitante']) === intval($user_id)
        || intval($informe['id_tecnico']) === intval($user_id);

    if (!$es_participante) {
        http_response_code(403);
        die("<div class='container mt-5 alert alert-danger'>Acceso denegado: no tienes permisos para ver este informe.</div>");
    }

    // Marcado automático como "Leido": lo realiza el SOLICITANTE al abrir su
    // informe (antes la condición exigía id_solicitante = usuario pero el
    // marcado debía ocurrir cuando el solicitante lo abría con estado
    // 'Pendiente', lo cual ya es correcto aquí porque se comprueba tras
    // verificar la participación).
    if ($rol !== 'admin'
        && intval($informe['id_solicitante']) === intval($user_id)
        && $informe['estado'] === 'Pendiente') {
        $stmt_up = $conexion->prepare(
            "UPDATE public.informes_tecnicos
             SET estado = 'Leido', fecha_actualizacion = now()
             WHERE id = :id AND estado = 'Pendiente'"
        );
        $stmt_up->execute([':id' => $id_informe]);
        $informe['estado'] = 'Leido';
    }
} catch (PDOException $e) {
    error_log('ver_informe.php: ' . $e->getMessage());
    die("<div class='container mt-5 alert alert-danger'>Error al consultar el informe.</div>");
}

$fecha_formateada = !empty($informe['fecha']) ? date('d/m/Y', strtotime($informe['fecha'])) : 'N/A';
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Informe N° <?= htmlspecialchars((string)$informe['id']) ?> - Fundasalud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 para alertas modernas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        /* ================= PANTALLA ================= */
        #document-to-print {
            max-width: 800px;
            min-height: 950px;
            display: flex;
            flex-direction: column;
            background: #fff;
            margin: 0 auto;
            padding: 2.5rem;
            border-radius: .5rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 0 15px rgba(0, 0, 0, .08);
        }

        /* La tabla estructural se comporta como flex vertical en pantalla */
        .print-table {
            display: flex;
            flex-direction: column;
            flex: 1 0 auto;
            width: 100%;
            border-collapse: collapse;
        }

        .print-table>tbody,
        .print-table>tbody>tr,
        .print-table>tbody>tr>td {
            display: block;
            width: 100%;
            padding: 0;
        }

        /* El espaciador de impresión NO se ve en pantalla */
        .print-table>tfoot {
            display: none;
        }

        .doc-footer {
            margin-top: 2rem;
            text-align: center;
        }

        .doc-footer img {
            width: 100%;
            height: auto;
        }

        /* ================= IMPRESIÓN ================= */
        @media print {
            @page {
                size: letter portrait;
                margin: 1.5cm;
            }

            .no-print {
                display: none !important;
            }

            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            #document-to-print {
                display: block !important;
                min-height: 0 !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            .print-table {
                display: table !important;
            }

            .print-table>tbody {
                display: table-row-group !important;
            }

            .print-table>tfoot {
                display: table-footer-group !important;
            }

            .print-table>tbody>tr,
            .print-table>tfoot>tr {
                display: table-row !important;
            }

            .print-table>tbody>tr>td,
            .print-table>tfoot>tr>td {
                display: table-cell !important;
                padding: 0 !important;
            }

            .spacer-cell {
                height: 3.2cm;
            }

            .doc-footer {
                position: fixed;
                left: 0;
                right: 0;
                bottom: 0;
                height: 3.2cm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff;
            }

            .doc-footer img {
                width: 100%;
                height: 3.2cm;
                object-fit: contain;
            }

            .doc-header {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            h4 {
                page-break-after: avoid;
                break-after: avoid;
            }

            .info-table {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            .signature-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body class="py-4">

    <!-- BARRA DE ACCIONES Y SELECTOR DE ESTADO (NO SE IMPRIME) -->
    <div class="container no-print mb-4" style="max-width: 800px;">
        <?php if ($rol === 'admin'): ?>
        <div class="row align-items-center bg-white p-3 rounded shadow-sm border mb-3">
            <div class="col-md-4 fw-bold text-secondary">
                <i class="fa-solid fa-sliders me-1"></i> Estado del Informe:
            </div>
            <div class="col-md-5">
                <select id="select-estado" class="form-select form-select-sm">
                    <option value="Pendiente" <?= $informe['estado'] === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Leido" <?= $informe['estado'] === 'Leido' ? 'selected' : '' ?>>Leído</option>
                    <option value="En Proceso" <?= $informe['estado'] === 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                    <option value="Resuelto" <?= $informe['estado'] === 'Resuelto' ? 'selected' : '' ?>>Resuelto</option>
                    <option value="Cancelado" <?= $informe['estado'] === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3 text-end mt-2 mt-md-0">
                <button type="button" onclick="actualizarEstado(<?= intval($informe['id']) ?>)" class="btn btn-sm btn-primary w-100">
                    <i class="fa-solid fa-floppy-disk me-1"></i> Actualizar
                </button>
            </div>
        </div>
        <?php else: ?>
        <div class="d-flex align-items-center bg-white p-3 rounded shadow-sm border mb-3">
            <span class="fw-bold text-secondary me-2"><i class="fa-solid fa-sliders me-1"></i> Estado del Informe:</span>
            <span class="badge bg-secondary"><?= htmlspecialchars((string)$informe['estado']) ?></span>
            <span class="text-muted small ms-3">(Solo los administradores pueden modificar el estado)</span>
        </div>
        <?php endif; ?>

        <div class="text-end">
            <a href="buzon.php" class="btn btn-secondary me-2">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al Buzón
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fa-solid fa-print me-1"></i> Imprimir / Guardar PDF
            </button>
        </div>
    </div>

    <!-- DOCUMENTO -->
    <div id="document-to-print">

        <table class="print-table">
            <tbody>
                <tr>
                    <td>
                        <!-- CABECERA -->
                        <header class="doc-header">
                            <div class="text-center mb-3">
                                <img src="imagenes/cabecera.png" alt="Cabecera Institucional Fundasalud" class="img-fluid w-100">
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 small fw-bold">
                                <div>
                                    <span>N° INFORME: </span>
                                    <span class="text-primary">N° <?= htmlspecialchars((string)$informe['id']) ?></span>
                                </div>
                                <div>
                                    <span>FECHA: </span>
                                    <span><?= $fecha_formateada ?></span>
                                </div>
                            </div>

                            <div class="border rounded p-3 bg-light">
                                <div class="mb-1">
                                    <strong>PARA:</strong> <?= htmlspecialchars($informe['para_nombre'] ?? 'N/A') ?>
                                    <?= !empty($informe['para_cargo']) ? ' - ' . htmlspecialchars($informe['para_cargo']) : '' ?>
                                </div>
                                <div>
                                    <strong>DE:</strong> <?= htmlspecialchars($informe['de_nombre'] ?? 'N/A') ?>
                                    <?= !empty($informe['de_cargo']) ? ' - ' . htmlspecialchars($informe['de_cargo']) : '' ?>
                                    <?= !empty($informe['de_depto']) ? ' (' . htmlspecialchars($informe['de_depto']) . ')' : '' ?>
                                </div>
                            </div>
                        </header>

                        <!-- CUERPO -->
                        <main class="doc-body">
                            <h4 class="text-center my-3 fw-bold text-uppercase" style="letter-spacing: 1px;">Informe Técnico</h4>

                            <p class="small mb-3 text-justify">
                                Reciban un cordial y respetuoso saludo, deseándole éxitos en el cumplimiento de sus funciones. La presente tiene como finalidad informarle sobre el diagnóstico del siguiente dispositivo:
                            </p>

                            <table class="table table-bordered align-middle mb-3 info-table">
                                <thead class="table-light text-center small">
                                    <tr>
                                        <th style="width: 33%;">DESCRIPCIÓN</th>
                                        <th style="width: 33%;">DIAGNÓSTICO - PROBLEMA</th>
                                        <th style="width: 34%;">OBSERVACIÓN</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr>
                                        <td style="white-space: pre-line;"><?= htmlspecialchars($informe['descripcion_equipo'] ?? '') ?></td>
                                        <td style="white-space: pre-line;"><?= htmlspecialchars($informe['diagnostico_problema'] ?? '') ?></td>
                                        <td style="white-space: pre-line;"><?= htmlspecialchars($informe['observacion'] ?? '') ?></td>
                                    </tr>
                                </tbody>
                            </table>

                            <p class="small mt-3 mb-1">Sin más a que hacer referencia, se despide,</p>
                            <p class="small fw-bold mb-3">Bolivarianamente,</p>

                            <div class="signature-block text-center mt-4 pt-2">
                                <div class="fw-bold text-uppercase border-top d-inline-block pt-1 px-5">
                                    <?= htmlspecialchars($informe['de_nombre'] ?? '') ?>
                                </div>
                                <div class="small text-muted"><?= htmlspecialchars($informe['de_cargo'] ?? '') ?></div>
                            </div>
                        </main>
                    </td>
                </tr>
            </tbody>

            <!-- ESPACIADOR: solo existe al imprimir; reserva el alto del pie en cada hoja -->
            <tfoot>
                <tr>
                    <td class="spacer-cell"></td>
                </tr>
            </tfoot>
        </table>

        <!-- PIE: fijo abajo en cada hoja impresa; al fondo de la tarjeta en pantalla -->
        <footer class="doc-footer">
            <img src="imagenes/pie_pagina.png" alt="Pie de Página Fundasalud">
        </footer>

    </div>

    <!-- SCRIPT AJAX PARA ACTUALIZAR ESTADO -->
    <script>
        async function actualizarEstado(idInforme) {
            const nuevoEstado = document.getElementById('select-estado').value;

            try {
                const response = await fetch('acciones/actualizar_estado_informe.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: idInforme,
                        estado: nuevoEstado
                    })
                });

                const res = await response.json();

                if (res.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Actualizado!',
                        text: 'El estado del informe cambió a: ' + nuevoEstado,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res.message || 'No se pudo actualizar el estado.'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Ocurrió un problema al comunicarse con el servidor.'
                });
            }
        }
    </script>
</body>

</html>