<?php
// includes/sidebar.php
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>
<div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
    <div class="text-center mb-4">
        <img src="imagenes/fundasalud.png" alt="Logo Fundasalud" class="img-fluid mt-3" style="max-width: 130px; height: auto;">
        <h5 class="mt-2">Fundasalud</h5>
        <p class="small text-white">Rol: <?php echo $_SESSION["rol"]; ?></p>
        <hr>
    </div>
    <ul class="nav flex-column gap-2">
        <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fa-solid fa-chart-line me-2"></i> Dashboard
            </a>
        </li>

        <?php if ($_SESSION["rol"] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link <?php echo ($pagina_actual == 'gestion_usuarios.php') ? 'active' : ''; ?>" href="gestion_usuarios.php">
                    <i class="fa-solid fa-users me-2"></i> Usuarios
                </a>
            </li>
        <?php endif; ?>

        <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'informes.php') ? 'active' : ''; ?>" href="#">
                <i class="fa-solid fa-file-invoice me-2"></i> Informes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($pagina_actual == 'configuracion.php') ? 'active' : ''; ?>" href="#">
                <i class="fa-solid fa-gear me-2"></i> Configuración
            </a>
        </li>
    </ul>
</div>