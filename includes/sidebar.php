<?php
// includes/sidebar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("config/config.php");
require_once("acciones/obtenerInformes.php");

// Es recomendable usar SCRIPT_NAME en lugar de PHP_SELF por seguridad
$pagina_actual = basename($_SERVER['SCRIPT_NAME']);

// Obtener el rol de forma segura para evitar el warning "Undefined array key" en PHP 8+
$rol = $_SESSION["rol"] ?? 'invitado';

// Obtener la cantidad de informes pendientes para el usuario actual
$total_pendientes = obtenerInformesPendientes($conexion, $_SESSION["id"] ?? 0);
?>

<div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">

    <div class="text-center mb-4">
        <img src="imagenes/fundasalud.png" alt="Logo Fundasalud" class="img-fluid mt-3" style="max-width: 130px; height: auto;">
        <h5 class="mt-2">Fundasalud</h5>
        <!-- htmlspecialchars previene posibles problemas de XSS si el rol contuviera caracteres especiales -->
        <p class="small text-white">Rol: <?php echo htmlspecialchars($rol); ?></p>
        <hr>
    </div>

    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fa-solid fa-chart-line me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'informes.php') ? 'active' : ''; ?>" href="informes.php">
                <i class="fa-solid fa-file-signature me-2"></i> Informes
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center justify-content-between <?php echo ($pagina_actual == 'buzon.php') ? 'active' : ''; ?>" href="buzon.php">
                <div>
                    <i class="fa-solid fa-inbox me-2"></i> Buzón
                </div>
                <?php if ($total_pendientes > 0): ?>
                    <span class="badge bg-danger rounded-pill">
                        <?= $total_pendientes ?>
                    </span>
                <?php endif; ?>
            </a>
        </li>

        <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'): ?>
            <li class="nav-item">
                <a href="inventario.php" class="nav-link <?php echo ($pagina_actual == 'inventario.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-boxes-stacked me-2"></i> Inventario
                </a>
            </li>
            <li class="nav-item">
                <a href="historial_mantenimientos.php" class="nav-link <?php echo ($pagina_actual == 'historial_mantenimientos.php') ? 'active' : ''; ?>">
                    <i class="fa-solid fa-clock-rotate-left me-2"></i> Historial Mantenimientos
                </a>
            </li>
        <?php endif; ?>

        <?php if ($rol === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_actual == 'gestion_usuarios.php') ? 'active' : ''; ?>" href="gestion_usuarios.php">
                    <i class="fa-solid fa-users me-2"></i> Usuarios
                </a>
            </li>
        <?php endif; ?>

        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="cerrar_sesion.php">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión
            </a>
        </li>

    </ul>

</div>