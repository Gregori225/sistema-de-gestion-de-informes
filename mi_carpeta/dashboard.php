<?php
session_start();
// Si la sesión no existe, lo regresamos al login
if (empty($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<h1>BIENVENIDO</h1>
<p>Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?>.</p>
<a href="logout.php">Cerrar Sesión</a>