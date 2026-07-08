<?php
session_start();

// SEGURIDAD: Si la sesión está vacía, no estás logueado. ¡Pa' fuera!
if (empty($_SESSION["id"])) {
    header("location: login.php");
    exit();
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

            <?php include("includes/sidebar.php"); ?>

            <div class="col-md-9 col-lg-10 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold">Bienvenido, <?php echo $_SESSION["nombre"]; ?></h2>
                        <p class="text-muted"><?php echo $_SESSION["cargo"]; ?></p>
                    </div>
                    <a href="cerrar_sesion.php" class="btn btn-logout px-4 py-2 rounded-pill">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Cerrar Sesión
                    </a>
                </div>

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="card card-stat p-4 bg-white text-center">
                            <i class="fa-solid fa-user-check fa-2x text-primary mb-2"></i>
                            <h4 class="fw-bold">Activo</h4>
                            <p class="text-muted mb-0">Estado del Usuario</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat p-4 bg-white text-center">
                            <i class="fa-solid fa-clock fa-2x text-warning mb-2"></i>
                            <h4 class="fw-bold">Pendientes</h4>
                            <p class="text-muted mb-0">Informes por revisar</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-stat p-4 bg-white text-center">
                            <i class="fa-solid fa-database fa-2x text-success mb-2"></i>
                            <h4 class="fw-bold">PostgreSQL</h4>
                            <p class="text-muted mb-0">Base de Datos Conectada</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 card card-stat p-5 bg-white shadow-sm">
                    <h3 class="text-center text-success mb-4">¡Sesión Iniciada con Éxito!</h3>
                    <div class="alert alert-success text-center">
                        Los datos que ves arriba provienen directamente de tu tabla de PostgreSQL de forma segura.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>