<?php
session_start();

if (isset($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["contrasena"])) {

        $usuario = $_POST["usuario"];
        $contrasena = $_POST["contrasena"];

        // 1. Usamos parámetros ($1, $2) para evitar SQL Injection
        // 2. Agregamos "AND activo = TRUE" para el Soft Delete
        $query = "SELECT * FROM usuarios WHERE usuario = $1 AND contrasena = $2 AND activo = TRUE";
        $sql = pg_query_params($conexion, $query, array($usuario, $contrasena));

        if ($datos = pg_fetch_object($sql)) {

            // GUARDAMOS TODOS LOS ELEMENTOS DE LA TABLA EN LA SESIÓN
            $_SESSION["id"] = $datos->id;
            $_SESSION["usuario"] = $datos->usuario;
            $_SESSION["nombre"] = $datos->nombre;
            $_SESSION["rol"] = $datos->rol;
            $_SESSION["cargo"] = $datos->cargo;
            $_SESSION["id_departamento"] = $datos->id_departamento;

            // Redirección limpia mediante JavaScript
            echo "<script>window.location.href = 'dashboard.php';</script>";
            exit();
        } else {
            echo "<div class='alert alert-danger text-center mb-3'>Acceso denegado o usuario inactivo</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center mb-3'>Por favor, completa todos los campos</div>";
    }
}
