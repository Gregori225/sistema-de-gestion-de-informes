<?php
session_start();

// SEGURIDAD: Solo administradores
if (empty($_SESSION["id"]) || ($_SESSION["rol"] ?? '') !== 'admin') {
    header("location: login.php");
    exit();
}

require_once("config/config.php");

// Capturar parámetros de filtro
$id_item = filter_var($_GET['id_item'] ?? null, FILTER_VALIDATE_INT);
$busqueda = trim($_GET['busqueda'] ?? '');

$itemInfo = null;

try {
    // Si se pasa un id_item, obtenemos los datos del bien
    if ($id_item) {
        $stmtItem = $conexion->prepare("SELECT * FROM public.inventario WHERE id = :id");
        $stmtItem->execute([':id' => $id_item]);
        $itemInfo = $stmtItem->fetch(PDO::FETCH_ASSOC);
    }

    // Consulta principal uniendo mantenimientos, inventario y usuarios
    $sql = "SELECT m.*, i.codigo_patrimonial, i.nombre AS nombre_bien, i.marca, i.modelo, i.serial, i.categoria, 
                   u.nombre AS nombre_tecnico, u.cargo AS cargo_tecnico
            FROM public.mantenimientos_inventario m
            JOIN public.inventario i ON m.id_item = i.id
            LEFT JOIN public.usuarios u ON m.id_tecnico = u.id
            WHERE 1=1";

    $params = [];

    if ($id_item) {
        $sql .= " AND m.id_item = :id_item";
        $params[':id_item'] = $id_item;
    }

    if (!empty($busqueda)) {
        $sql .= " AND (i.codigo_patrimonial ILIKE :busqueda OR i.nombre ILIKE :busqueda OR m.descripcion_trabajo ILIKE :busqueda OR u.nombre ILIKE :busqueda)";
        $params[':busqueda'] = "%" . $busqueda . "%";
    }

    $sql .= " ORDER BY m.fecha_mantenimiento DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $listaMantenimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar historial de mantenimientos: " . $e->getMessage());
    $listaMantenimientos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Mantenimientos - Fundasalud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        @media print {

            .no-print,
            .sidebar {
                display: none !important;
            }

            .main-content {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background-color: #fff !important;
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- MENÚ LATERAL -->
            <?php include("includes/sidebar.php"); ?>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-md-9 col-lg-10 main-content p-4">

                <!-- ENCABEZADO -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 no-print">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="fa-solid fa-screwdriver-wrench text-warning me-2"></i> Historial de Mantenimientos</h2>
                        <p class="text-muted mb-0">Registro y trazabilidad de revisiones, reparaciones y soportes técnicos</p>
                    </div>
                    <div class="d-flex gap-2">
                        <button onclick="window.print();" class="btn btn-outline-dark shadow-sm">
                            <i class="fa-solid fa-print me-1"></i> Imprimir Reporte
                        </button>
                        <a href="inventario.php" class="btn btn-secondary shadow-sm">
                            <i class="fa-solid fa-arrow-left me-1"></i> Volver al Inventario
                        </a>
                    </div>
                </div>

                <!-- ENCABEZADO INSTITUCIONAL PARA IMPRESIÓN -->
                <div class="d-none d-print-block text-center mb-4 border-bottom pb-3">
                    <h4 class="fw-bold text-dark mb-1">FUNDACIÓN TRUJILLANA DE LA SALUD (FUNDASALUD)</h4>
                    <p class="text-muted small mb-0">Coordinación de Informática - Reporte Histórico de Mantenimiento de Bienes</p>
                </div>

                <!-- FICHA RESUMEN DEL BIEN -->
                <?php if ($itemInfo): ?>
                    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
                        <div class="card-body p-4">
                            <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-circle-info me-2"></i> Ficha del Bien Evaluado</h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Código de Bien:</small>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($itemInfo['codigo_patrimonial']); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Nombre / Descripción:</small>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($itemInfo['nombre']); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Categoría:</small>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($itemInfo['categoria']); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Marca / Modelo:</small>
                                    <span><?= htmlspecialchars($itemInfo['marca'] ?? 'N/D'); ?> / <?= htmlspecialchars($itemInfo['modelo'] ?? 'N/D'); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Serial:</small>
                                    <span><?= htmlspecialchars($itemInfo['serial'] ?? 'Sin serial'); ?></span>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Estado Actual:</small>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($itemInfo['estado']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- BUSCADOR GENERAL (Si no se filtró por un bien fijo) -->
                <?php if (!$id_item): ?>
                    <div class="card shadow-sm border-0 rounded-3 mb-4 p-3 bg-white no-print">
                        <form method="GET" action="historial_mantenimientos.php" class="row g-3 align-items-center">
                            <div class="col-md-10">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" name="busqueda" value="<?= htmlspecialchars($busqueda); ?>" placeholder="Buscar por código, nombre del bien, descripción del trabajo o técnico...">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-filter me-1"></i> Buscar</button>
                                <?php if (!empty($busqueda)): ?>
                                    <a href="historial_mantenimientos.php" class="btn btn-outline-secondary" title="Limpiar"><i class="fa-solid fa-rotate-left"></i></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- TABLA DE HISTORIAL -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="py-3 ps-3">Fecha y Hora</th>
                                        <?php if (!$id_item): ?>
                                            <th class="py-3">Bien / Código</th>
                                        <?php endif; ?>
                                        <th class="py-3">Tipo</th>
                                        <th class="py-3">Técnico Responsable</th>
                                        <th class="py-3">Descripción del Trabajo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($listaMantenimientos)): ?>
                                        <tr>
                                            <td colspan="<?= $id_item ? '4' : '5'; ?>" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-folder-open fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                                <h5 class="fw-semibold">No se encontraron registros de mantenimiento</h5>
                                                <p class="small mb-0">No hay intervenciones técnicas registradas con los criterios actuales.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($listaMantenimientos as $mant): ?>
                                            <tr>
                                                <td class="ps-3 fw-semibold text-dark text-nowrap">
                                                    <?= date('d/m/Y H:i', strtotime($mant['fecha_mantenimiento'])); ?>
                                                </td>
                                                <?php if (!$id_item): ?>
                                                    <td>
                                                        <span class="fw-bold text-dark d-block"><?= htmlspecialchars($mant['codigo_patrimonial']); ?></span>
                                                        <small class="text-muted"><?= htmlspecialchars($mant['nombre_bien']); ?></small>
                                                    </td>
                                                <?php endif; ?>
                                                <td>
                                                    <?php
                                                    $badgeType = 'bg-secondary';
                                                    if ($mant['tipo_mantenimiento'] === 'Preventivo') $badgeType = 'bg-success';
                                                    elseif ($mant['tipo_mantenimiento'] === 'Correctivo') $badgeType = 'bg-danger';
                                                    elseif ($mant['tipo_mantenimiento'] === 'Revisión') $badgeType = 'bg-info text-dark';
                                                    ?>
                                                    <span class="badge <?= $badgeType; ?>">
                                                        <?= htmlspecialchars($mant['tipo_mantenimiento']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-semibold text-secondary d-block"><?= htmlspecialchars($mant['nombre_tecnico'] ?? 'Técnico externo / No especificado'); ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($mant['cargo_tecnico'] ?? ''); ?></small>
                                                </td>
                                                <td class="py-3">
                                                    <p class="mb-0 text-dark"><?= nl2br(htmlspecialchars($mant['descripcion_trabajo'])); ?></p>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>