<?php
session_start();

// SEGURIDAD: Verificar sesión activa
if (empty($_SESSION["id"])) {
    header("location: login.php");
    exit();
}

$rol = $_SESSION["rol"] ?? 'invitado';

// RESTRICCIÓN: Solo los administradores pueden acceder al inventario
if ($rol !== 'admin') {
    header("location: dashboard.php");
    exit();
}

require_once("config/config.php");

// Capturar parámetros de búsqueda y categoría si existen
$busqueda = trim($_GET['busqueda'] ?? '');
$categoria_filtro = trim($_GET['categoria'] ?? '');

try {
    // Consulta SQL simplificada sin id_responsable
    $sql = "SELECT i.* 
            FROM public.inventario i 
            WHERE 1=1";

    $params = [];

    // Aplicar filtro de búsqueda (Código, Nombre o Serial)
    if (!empty($busqueda)) {
        $sql .= " AND (i.codigo_patrimonial ILIKE :busqueda OR i.nombre ILIKE :busqueda OR i.serial ILIKE :busqueda)";
        $params[':busqueda'] = "%" . $busqueda . "%";
    }

    // Aplicar filtro por categoría
    if (!empty($categoria_filtro)) {
        $sql .= " AND i.categoria = :categoria";
        $params[':categoria'] = $categoria_filtro;
    }

    $sql .= " ORDER BY i.id DESC";

    $stmt = $conexion->prepare($sql);
    $stmt->execute($params);
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener lista de usuarios activos para asignarlos como técnicos en los mantenimientos
    $stmtTecnicos = $conexion->query("SELECT id, nombre, cargo FROM public.usuarios WHERE activo = true ORDER BY nombre ASC");
    $listaTecnicos = $stmtTecnicos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al cargar inventario: " . $e->getMessage());
    $inventario = [];
    $listaTecnicos = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Inventario - Fundasalud</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

    <div class="container-fluid">
        <div class="row">

            <!-- MENÚ LATERAL -->
            <?php include("includes/sidebar.php"); ?>

            <!-- CONTENIDO PRINCIPAL -->
            <div class="col-md-9 col-lg-10 main-content p-4">

                <!-- ENCABEZADO -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                    <div>
                        <h2 class="fw-bold mb-1"><i class="fa-solid fa-boxes-stacked text-primary me-2"></i> Módulo de Inventario</h2>
                        <p class="text-muted mb-0">Gestión de equipos tecnológicos, herramientas y bienes institucionales</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNuevoItem">
                            <i class="fa-solid fa-plus me-1"></i> Registrar Bien
                        </button>
                    </div>
                </div>

                <!-- BARRA DE FILTROS Y BUSCADOR -->
                <div class="card shadow-sm border-0 rounded-3 mb-4 p-3 bg-white">
                    <form method="GET" action="inventario.php" class="row g-3 align-items-center">

                        <!-- Buscador -->
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" name="busqueda" value="<?= htmlspecialchars($busqueda); ?>" placeholder="Buscar por código de bien, nombre o serial...">
                            </div>
                        </div>

                        <!-- Filtro por Categoría -->
                        <div class="col-md-4">
                            <select name="categoria" class="form-select">
                                <option value="">Todas las Categorías</option>
                                <option value="Equipo Tecnológico" <?= $categoria_filtro === 'Equipo Tecnológico' ? 'selected' : ''; ?>>Equipo Tecnológico</option>
                                <option value="Herramienta" <?= $categoria_filtro === 'Herramienta' ? 'selected' : ''; ?>>Herramienta</option>
                                <option value="Refacciones" <?= $categoria_filtro === 'Refacciones' ? 'selected' : ''; ?>>Refacciones</option>
                            </select>
                        </div>

                        <!-- Botones de Acción del Filtro -->
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-dark w-100"><i class="fa-solid fa-filter me-1"></i> Filtrar</button>
                            <?php if (!empty($busqueda) || !empty($categoria_filtro)): ?>
                                <a href="inventario.php" class="btn btn-outline-secondary" title="Limpiar filtros"><i class="fa-solid fa-rotate-left"></i></a>
                            <?php endif; ?>
                        </div>

                    </form>
                </div>

                <!-- TABLA DE INVENTARIO -->
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-secondary small text-uppercase">
                                    <tr>
                                        <th class="py-3 ps-3">Código de Bien</th>
                                        <th class="py-3">Nombre / Descripción</th>
                                        <th class="py-3">Categoría</th>
                                        <th class="py-3">Ubicación</th>
                                        <th class="py-3">Estado</th>
                                        <th class="py-3 text-end pe-3">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($inventario)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-folder-open fa-3x mb-3 d-block text-secondary opacity-50"></i>
                                                <h5 class="fw-semibold">No se encontraron registros</h5>
                                                <p class="small mb-0">Intenta con otros términos de búsqueda o limpia los filtros aplicados.</p>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($inventario as $item): ?>
                                            <tr>
                                                <td class="ps-3 fw-bold text-dark"><?= htmlspecialchars($item['codigo_patrimonial']); ?></td>
                                                <td>
                                                    <span class="fw-semibold text-secondary d-block"><?= htmlspecialchars($item['nombre']); ?></span>
                                                    <small class="text-muted"><?= htmlspecialchars($item['marca'] ?? '') . ' ' . htmlspecialchars($item['modelo'] ?? ''); ?> <?= !empty($item['serial']) ? '• S/N: ' . htmlspecialchars($item['serial']) : ''; ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?= htmlspecialchars($item['categoria']); ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($item['ubicacion_actual'] ?? 'No especificada'); ?></td>
                                                <td>
                                                    <?php
                                                    $badgeBg = 'bg-secondary';
                                                    if ($item['estado'] === 'Disponible') $badgeBg = 'bg-success';
                                                    elseif ($item['estado'] === 'En Uso') $badgeBg = 'bg-primary';
                                                    elseif ($item['estado'] === 'En Mantenimiento') $badgeBg = 'bg-warning text-dark';
                                                    elseif ($item['estado'] === 'Dado de Baja') $badgeBg = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?= $badgeBg ?>"><?= htmlspecialchars($item['estado']); ?></span>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <!-- Botón Ver Detalles (Modal Informativo) -->
                                                    <button class="btn btn-sm btn-outline-info me-1 btn-ver-detalle"
                                                        title="Ver Ficha Técnica"
                                                        data-id="<?= $item['id']; ?>"
                                                        data-codigo="<?= htmlspecialchars($item['codigo_patrimonial']); ?>"
                                                        data-nombre="<?= htmlspecialchars($item['nombre']); ?>"
                                                        data-categoria="<?= htmlspecialchars($item['categoria']); ?>"
                                                        data-marca="<?= htmlspecialchars($item['marca'] ?? ''); ?>"
                                                        data-modelo="<?= htmlspecialchars($item['modelo'] ?? ''); ?>"
                                                        data-serial="<?= htmlspecialchars($item['serial'] ?? ''); ?>"
                                                        data-ubicacion="<?= htmlspecialchars($item['ubicacion_actual'] ?? ''); ?>"
                                                        data-estado="<?= htmlspecialchars($item['estado']); ?>"
                                                        data-observaciones="<?= htmlspecialchars($item['observaciones'] ?? ''); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalDetalleItem">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>

                                                    <!-- Botón Ver Historial de Mantenimientos -->
                                                    <a href="historial_mantenimientos.php?id_item=<?= $item['id']; ?>"
                                                        class="btn btn-sm btn-outline-secondary me-1"
                                                        title="Ver Historial de Mantenimientos">
                                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                                    </a>

                                                    <!-- Botón Registrar Mantenimiento -->
                                                    <button class="btn btn-sm btn-outline-warning me-1 btn-mantenimiento"
                                                        title="Registrar Mantenimiento"
                                                        data-id="<?= $item['id']; ?>"
                                                        data-codigo="<?= htmlspecialchars($item['codigo_patrimonial']); ?>"
                                                        data-nombre="<?= htmlspecialchars($item['nombre']); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalMantenimiento">
                                                        <i class="fa-solid fa-screwdriver-wrench text-dark"></i>
                                                    </button>

                                                    <!-- Botón Editar -->
                                                    <button class="btn btn-sm btn-outline-primary me-1 btn-editar"
                                                        title="Editar"
                                                        data-id="<?= $item['id']; ?>"
                                                        data-codigo="<?= htmlspecialchars($item['codigo_patrimonial']); ?>"
                                                        data-nombre="<?= htmlspecialchars($item['nombre']); ?>"
                                                        data-categoria="<?= htmlspecialchars($item['categoria']); ?>"
                                                        data-marca="<?= htmlspecialchars($item['marca'] ?? ''); ?>"
                                                        data-modelo="<?= htmlspecialchars($item['modelo'] ?? ''); ?>"
                                                        data-serial="<?= htmlspecialchars($item['serial'] ?? ''); ?>"
                                                        data-ubicacion="<?= htmlspecialchars($item['ubicacion_actual'] ?? ''); ?>"
                                                        data-estado="<?= htmlspecialchars($item['estado']); ?>"
                                                        data-observaciones="<?= htmlspecialchars($item['observaciones'] ?? ''); ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalEditarItem">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>

                                                    <!-- Botón Eliminar -->
                                                    <a href="acciones/eliminar_item.php?id=<?= $item['id']; ?>"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Está seguro de que desea eliminar este bien del inventario? Esta acción no se puede deshacer.');">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
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

    <!-- MODAL DE DETALLES / FICHA TÉCNICA -->
    <div class="modal fade" id="modalDetalleItem" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title fw-bold" id="modalDetalleLabel">
                        <i class="fa-solid fa-circle-info me-2"></i> Ficha Técnica del Bien
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Código Patrimonial:</small>
                            <span id="det_codigo" class="fw-bold text-dark fs-6"></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Nombre / Descripción:</small>
                            <span id="det_nombre" class="fw-bold text-dark fs-6"></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Categoría:</small>
                            <span id="det_categoria" class="badge bg-secondary"></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Estado Actual:</small>
                            <span id="det_estado" class="badge"></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Marca / Modelo:</small>
                            <span id="det_marca_modelo" class="text-dark fw-semibold"></span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Serial:</small>
                            <span id="det_serial" class="text-dark fw-semibold"></span>
                        </div>
                        <div class="col-md-12">
                            <small class="text-muted d-block">Ubicación Actual:</small>
                            <span id="det_ubicacion" class="text-dark fw-semibold"></span>
                        </div>
                        <div class="col-md-12">
                            <small class="text-muted d-block">Observaciones:</small>
                            <p id="det_observaciones" class="text-dark bg-light p-3 rounded mb-0 border"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <a href="#" id="btnVerHistorialModal" class="btn btn-warning text-dark fw-semibold shadow-sm">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Ver Historial de Mantenimientos
                    </a>
                    <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA REGISTRAR NUEVO BIEN -->
    <div class="modal fade" id="modalNuevoItem" tabindex="-1" aria-labelledby="modalNuevoItemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="modalNuevoItemLabel">
                        <i class="fa-solid fa-box-archive me-2"></i> Registrar Nuevo Bien / Herramienta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="acciones/guardar_item.php" method="POST">
                    <div class="modal-body p-4">
                        <div class="row g-3">

                            <!-- Código de Bien -->
                            <div class="col-md-6">
                                <label for="codigo_patrimonial" class="form-label fw-semibold">Código de Bien <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo_patrimonial" name="codigo_patrimonial" required placeholder="Ej: BIEN-FS-00123">
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-6">
                                <label for="categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="categoria" name="categoria" required>
                                    <option value="" selected disabled>Seleccione categoría...</option>
                                    <option value="Equipo Tecnológico">Equipo Tecnológico</option>
                                    <option value="Herramienta">Herramienta</option>
                                    <option value="Refacciones">Refacciones</option>
                                </select>
                            </div>

                            <!-- Nombre / Descripción -->
                            <div class="col-md-12">
                                <label for="nombre" class="form-label fw-semibold">Nombre / Descripción del Bien <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Ej: Computadora de Escritorio Core i5 / Taladro Percutor">
                            </div>

                            <!-- Marca -->
                            <div class="col-md-4">
                                <label for="marca" class="form-label fw-semibold">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca" placeholder="Ej: HP, Bosch">
                            </div>

                            <!-- Modelo -->
                            <div class="col-md-4">
                                <label for="modelo" class="form-label fw-semibold">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Ej: ProBook 450">
                            </div>

                            <!-- Serial -->
                            <div class="col-md-4">
                                <label for="serial" class="form-label fw-semibold">Serial</label>
                                <input type="text" class="form-control" id="serial" name="serial" placeholder="Ej: S/N 5XYZ890">
                            </div>

                            <!-- Ubicación Actual -->
                            <div class="col-md-6">
                                <label for="ubicacion_actual" class="form-label fw-semibold">Ubicación Actual</label>
                                <input type="text" class="form-control" id="ubicacion_actual" name="ubicacion_actual" placeholder="Ej: Oficina de Informática, Depósito">
                            </div>

                            <!-- Estado Inicial -->
                            <div class="col-md-6">
                                <label for="estado" class="form-label fw-semibold">Estado Inicial</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="Disponible" selected>Disponible</option>
                                    <option value="En Uso">En Uso</option>
                                    <option value="En Mantenimiento">En Mantenimiento</option>
                                </select>
                            </div>

                            <!-- Observaciones -->
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="2" placeholder="Detalles adicionales sobre el estado o condición del bien..."></textarea>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Bien</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA EDITAR BIEN -->
    <div class="modal fade" id="modalEditarItem" tabindex="-1" aria-labelledby="modalEditarItemLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold" id="modalEditarItemLabel">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Editar Bien / Herramienta
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="acciones/editar_item.php" method="POST">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="edit_codigo" class="form-label fw-semibold">Código de Bien <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_codigo" name="codigo_patrimonial" required>
                            </div>

                            <div class="col-md-6">
                                <label for="edit_categoria" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_categoria" name="categoria" required>
                                    <option value="Equipo Tecnológico">Equipo Tecnológico</option>
                                    <option value="Herramienta">Herramienta</option>
                                    <option value="Refacciones">Refacciones</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="edit_nombre" class="form-label fw-semibold">Nombre / Descripción del Bien <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                            </div>

                            <div class="col-md-4">
                                <label for="edit_marca" class="form-label fw-semibold">Marca</label>
                                <input type="text" class="form-control" id="edit_marca" name="marca">
                            </div>

                            <div class="col-md-4">
                                <label for="edit_modelo" class="form-label fw-semibold">Modelo</label>
                                <input type="text" class="form-control" id="edit_modelo" name="modelo">
                            </div>

                            <div class="col-md-4">
                                <label for="edit_serial" class="form-label fw-semibold">Serial</label>
                                <input type="text" class="form-control" id="edit_serial" name="serial">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_ubicacion" class="form-label fw-semibold">Ubicación Actual</label>
                                <input type="text" class="form-control" id="edit_ubicacion" name="ubicacion_actual">
                            </div>

                            <div class="col-md-6">
                                <label for="edit_estado" class="form-label fw-semibold">Estado</label>
                                <select class="form-select" id="edit_estado" name="estado">
                                    <option value="Disponible">Disponible</option>
                                    <option value="En Uso">En Uso</option>
                                    <option value="En Mantenimiento">En Mantenimiento</option>
                                    <option value="Dado de Baja">Dado de Baja</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="edit_observaciones" class="form-label fw-semibold">Observaciones</label>
                                <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL PARA REGISTRAR MANTENIMIENTO -->
    <div class="modal fade" id="modalMantenimiento" tabindex="-1" aria-labelledby="modalMantenimientoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalMantenimientoLabel">
                        <i class="fa-solid fa-screwdriver-wrench me-2"></i> Registrar Mantenimiento / Revisión
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="acciones/guardar_mantenimiento.php" method="POST">
                    <input type="hidden" id="mant_id_item" name="id_item">

                    <div class="modal-body p-4">
                        <div class="alert alert-light border mb-3">
                            <small class="text-muted d-block">Bien seleccionado:</small>
                            <span class="fw-bold text-dark" id="mant_info_bien"></span>
                        </div>

                        <div class="row g-3">
                            <!-- Tipo de Mantenimiento -->
                            <div class="col-md-6">
                                <label for="mant_tipo" class="form-label fw-semibold">Tipo de Mantenimiento <span class="text-danger">*</span></label>
                                <select class="form-select" id="mant_tipo" name="tipo_mantenimiento" required>
                                    <option value="" selected disabled>Seleccione tipo...</option>
                                    <option value="Preventivo">Preventivo</option>
                                    <option value="Correctivo">Correctivo</option>
                                    <option value="Revision">Revision</option>
                                </select>
                            </div>

                            <!-- Fecha y Hora del Mantenimiento -->
                            <div class="col-md-6">
                                <label for="mant_fecha" class="form-label fw-semibold">Fecha y Hora <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="mant_fecha" name="fecha_mantenimiento" value="<?= date('Y-m-d\TH:i'); ?>" required>
                            </div>

                            <!-- Técnico Responsable -->
                            <div class="col-md-12">
                                <label for="mant_tecnico" class="form-label fw-semibold">Técnico Responsable <span class="text-danger">*</span></label>
                                <select class="form-select" id="mant_tecnico" name="id_tecnico" required>
                                    <option value="" selected disabled>Seleccione al técnico encargado...</option>
                                    <?php foreach ($listaTecnicos as $tec): ?>
                                        <option value="<?= $tec['id']; ?>">
                                            <?= htmlspecialchars($tec['nombre']); ?> (<?= htmlspecialchars($tec['cargo']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Descripción del Trabajo -->
                            <div class="col-md-12">
                                <label for="mant_descripcion" class="form-label fw-semibold">Descripción del Trabajo Realizado <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="mant_descripcion" name="descripcion_trabajo" rows="3" required placeholder="Detalle las reparaciones, ajustes o diagnósticos aplicados..."></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light px-4 py-3">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning fw-semibold">Guardar Mantenimiento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- SCRIPTS JS PARA DETALLES, EDICIÓN Y MANTENIMIENTO -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Script para poblar el modal de detalles / ficha técnica
            const detailButtons = document.querySelectorAll(".btn-ver-detalle");
            detailButtons.forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("det_codigo").textContent = this.dataset.codigo || 'N/D';
                    document.getElementById("det_nombre").textContent = this.dataset.nombre || 'N/D';
                    document.getElementById("det_categoria").textContent = this.dataset.categoria || 'N/D';
                    document.getElementById("det_serial").textContent = this.dataset.serial || 'Sin serial';
                    document.getElementById("det_ubicacion").textContent = this.dataset.ubicacion || 'No especificada';
                    document.getElementById("det_observaciones").textContent = this.dataset.observaciones || 'Sin observaciones registradas.';
                    document.getElementById("det_marca_modelo").textContent = (this.dataset.marca || 'N/D') + ' / ' + (this.dataset.modelo || 'N/D');

                    // Estilizar el badge de estado en el modal de detalles
                    const spanEstado = document.getElementById("det_estado");
                    const estado = this.dataset.estado;
                    spanEstado.textContent = estado;
                    spanEstado.className = "badge ";
                    if (estado === 'Disponible') spanEstado.classList.add('bg-success');
                    else if (estado === 'En Uso') spanEstado.classList.add('bg-primary');
                    else if (estado === 'En Mantenimiento') spanEstado.classList.add('bg-warning', 'text-dark');
                    else spanEstado.classList.add('bg-secondary');

                    // Configurar el enlace directo al historial de este ítem específico
                    document.getElementById("btnVerHistorialModal").href = "historial_mantenimientos.php?id_item=" + this.dataset.id;
                });
            });

            // Script para poblar el modal de edición
            const editButtons = document.querySelectorAll(".btn-editar");
            editButtons.forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("edit_id").value = this.dataset.id;
                    document.getElementById("edit_codigo").value = this.dataset.codigo;
                    document.getElementById("edit_nombre").value = this.dataset.nombre;
                    document.getElementById("edit_categoria").value = this.dataset.categoria;
                    document.getElementById("edit_marca").value = this.dataset.marca;
                    document.getElementById("edit_modelo").value = this.dataset.modelo;
                    document.getElementById("edit_serial").value = this.dataset.serial;
                    document.getElementById("edit_ubicacion").value = this.dataset.ubicacion;
                    document.getElementById("edit_estado").value = this.dataset.estado;
                    document.getElementById("edit_observaciones").value = this.dataset.observaciones;
                });
            });

            // Script para poblar el modal de mantenimiento
            const mantButtons = document.querySelectorAll(".btn-mantenimiento");
            mantButtons.forEach(button => {
                button.addEventListener("click", function() {
                    document.getElementById("mant_id_item").value = this.dataset.id;
                    document.getElementById("mant_info_bien").textContent = "[" + this.dataset.codigo + "] " + this.dataset.nombre;
                });
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>