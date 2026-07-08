<?php
require_once("config/config.php");
session_start();

// Función para verificar sesión activa
function verificarSesion() {
    if (empty($_SESSION["id"])) {
        header("location: login.php");
        exit();
    }
}

if (isset($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["contrasena"])) {

        $usuario = trim($_POST["usuario"]);
        $contrasena = $_POST["contrasena"];

        // 1. Usamos parámetros ($1, $2) para evitar SQL Injection
        // 2. La columna correcta es 'contrasena_hash' según Structure.sql
        $query = "SELECT * FROM usuarios WHERE usuario = $1 AND activo = TRUE";
        $sql = pg_query_params($conexion_pg, $query, array($usuario));

        if ($datos = pg_fetch_object($sql)) {
            // 3. Verificar contraseña en texto plano
<<<<<<< HEAD
            if ($contrasena === $datos->contrasena) {
=======
            if ($contrasena === $datos->contrasena_hash) {
>>>>>>> b86884843f9ecf0a5a8207f5e627ccdbab1c6451

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
                echo "<div class='alert alert-danger text-center mb-3'>Contraseña incorrecta</div>";
            }
        } else {
            echo "<div class='alert alert-danger text-center mb-3'>Usuario no encontrado o inactivo</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center mb-3'>Por favor, completa todos los campos</div>";
    }
}
