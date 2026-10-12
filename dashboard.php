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

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>