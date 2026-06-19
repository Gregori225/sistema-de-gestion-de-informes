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
    <style>
        :root {
            --primary-green: #00a859;
            --dark-blue: #1e293b;
        }

        body {
            background-color: #f1f5f9;
        }

        .sidebar {
            min-height: 100vh;
            background-color: var(--dark-blue);
            color: white;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.7);
            transition: 0.3s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
        }

        .main-content {
            padding: 2rem;
        }

        .card-stat {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .btn-logout {
            background-color: var(--primary-green);
            border: none;
            color: white;
        }

        .btn-logout:hover {
            background-color: #008a4a;
            color: white;
        }
    </style>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-house-medical fa-3x text-success mt-3"></i>
                    <h5 class="mt-2">Fundasalud</h5>
                    <p class="small text-muted">Rol: <?php echo $_SESSION["rol"]; ?></p>
                    <hr>
                </div>
                <ul class="nav flex-column gap-2">
                    <li class="nav-item"><a class="nav-link active" href="#"><i class="fa-solid fa-chart-line me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-users me-2"></i> Usuarios</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-file-invoice me-2"></i> Informes</a></li>
                    <li class="nav-item"><a class="nav-link" href="#"><i class="fa-solid fa-gear me-2"></i> Configuración</a></li>
                </ul>
            </div>

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