<?php
// acciones/getUltimoUsuario.php
require_once("../config/config.php");

try {
    // Buscamos el último usuario registrado (LIMIT 1) con el nombre de su departamento
    $sql = "SELECT u.id, u.usuario, u.nombre, u.rol, u.cargo, u.activo, d.nombre AS departamento 
            FROM public.usuarios u
            INNER JOIN public.departamentos d ON u.id_departamento = d.id 
            ORDER BY u.id DESC 
            LIMIT 1";

    $stmt = $conexion->query($sql);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Le decimos al navegador que la respuesta es un objeto JSON
    header('Content-Type: application/json');
    echo json_encode($usuario);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(["error" => $e->getMessage()]);
}
