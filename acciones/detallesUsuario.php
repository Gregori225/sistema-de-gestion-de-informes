<?php
// acciones/detallesUsuario.php

// 1. Incluimos la conexión a la base de datos
include_once("../models/conexion_bd.php");

// Verificamos que el ID venga por la URL y sea un número válido
$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$idUsuario) {
    http_response_code(400);
    echo json_encode(["error" => "ID de usuario no proporcionado o inválido."]);
    exit;
}

try {
    // 2. Consultamos los datos exactos del usuario en Postgres
    $sql = "SELECT id, nombre, usuario, rol, cargo, id_departamento, activo 
            FROM public.usuarios 
            WHERE id = :id 
            LIMIT 1";

    $stmt = $conexionPDO->prepare($sql);
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Importante: Postgres devuelve el booleano como un tipo nativo o "t"/"f".
        // Forzamos que en el JSON viaje como true o false real para el JavaScript.
        $usuario['activo'] = (bool)$usuario['activo'];

        // 3. Respondemos al JavaScript con los datos limpios
        header('Content-Type: application/json');
        echo json_encode($usuario);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "Usuario no encontrado."]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error en la base de datos: " . $e->getMessage()]);
}
