<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once("../config/config.php");

    // Capturamos los campos reales de tu tabla public.usuarios
    $usuario         = trim($_POST['usuario']);
    // Hash de contraseña seguro con password_hash()
    $contrasena      = password_hash(trim($_POST['contrasena']), PASSWORD_DEFAULT);
    $nombre          = trim($_POST['nombre']);
    $rol             = trim($_POST['rol']); 
    $cargo           = trim($_POST['cargo']);
    $id_departamento = intval($_POST['id_departamento']); 

    // Consulta adaptada a tu esquema de PostgreSQL
    $sql = "INSERT INTO public.usuarios (usuario, contrasena_hash, nombre, rol, cargo, id_departamento) 
            VALUES (:usuario, :contrasena_hash, :nombre, :rol, :cargo, :id_departamento)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':contrasena_hash', $contrasena);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':rol', $rol);
    $stmt->bindParam(':cargo', $cargo);
    $stmt->bindParam(':id_departamento', $id_departamento);

    if ($stmt->execute()) {
        header("location:../");
        exit;
    } else {
        echo "Error al crear el registro: " . implode(", ", $stmt->errorInfo());
    }
}

/**
 * Función para obtener todos los usuarios con su departamento
 */
function obtenerUsuarios($conexion)
{
    // Usamos un JOIN para traer el nombre del departamento y no solo el número de ID
    $sql = "SELECT u.id, u.usuario, u.nombre, u.rol, u.cargo, u.activo, d.nombre AS departamento 
            FROM public.usuarios u
            INNER JOIN public.departamentos d ON u.id_departamento = d.id 
            ORDER BY u.id ASC";

    $stmt = $conexion->query($sql);
    if (!$stmt) {
        return false;
    }
    return $stmt;
}


function obtenerDepartamentos($conexion)
{
    $sql = "SELECT id, nombre FROM public.departamentos ORDER BY id ASC";
    return $conexion->query($sql);
}
