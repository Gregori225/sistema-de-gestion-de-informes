<?php
session_start(); // Habilitamos el uso de sesiones al procesar el formulario

if (isset($_POST["btningresar"])) {
    if (!empty($_POST["usuario"]) && !empty($_POST["contrasena"])) {
        
        $usuario = $_POST["usuario"];
        $contrasena = $_POST["contrasena"];
        
        // Consultamos a la base de datos PostgreSQL
        $sql = pg_query($conexion, "SELECT * FROM usuarios WHERE usuario='$usuario' AND contrasena='$contrasena'");
        
        if ($datos = pg_fetch_object($sql)) {
            
            // GUARDAMOS TODOS LOS ELEMENTOS DE LA TABLA EN LA SESIÓN
            $_SESSION["id"] = $datos->id;
            $_SESSION["usuario"] = $datos->usuario;
            $_SESSION["nombre"] = $datos->nombre;
            $_SESSION["rol"] = $datos->rol;
            $_SESSION["cargo"] = $datos->cargo;
            $_SESSION["id_departamento"] = $datos->id_departamento; // <-- Añadido el elemento faltante de la tabla

            // Redirección limpia mediante JavaScript
            echo "<script>window.location.href = 'dashboard.php';</script>";
            exit(); 
        } else {
            echo "<div class='alert alert-danger text-center mb-3'>Acceso denegado</div>";
        }
        
    } else {
        echo "<div class='alert alert-danger text-center mb-3'>Por favor, completa todos los campos</div>";
    }
}
?>