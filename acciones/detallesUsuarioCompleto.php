<?php
// acciones/detallesUsuarioCompleto.php
require_once __DIR__ . '/../includes/auth.php';

// SEGURIDAD: requiere sesión activa y rol de administrador (solo la pantalla
// de gestión de usuarios consume este endpoint)
requerirRol('admin');

require_once(__DIR__ . "/../config/config.php");

$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$idUsuario) {
    http_response_code(400);
    echo json_encode(["error" => "ID de usuario inválido."]);
    exit;
}

try {
    // Cruce de tablas (INNER JOIN) para extraer el nombre del departamento
    $sql = "SELECT u.id, u.usuario, u.nombre, u.rol, u.cargo, u.activo, d.nombre AS departamento 
            FROM public.usuarios u
            INNER JOIN public.departamentos d ON u.id_departamento = d.id 
            WHERE u.id = :id 
            LIMIT 1";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuario['activo'] = (bool)$usuario['activo'];
        header('Content-Type: application/json');
        echo json_encode($usuario);
    } else {
        http_response_code(404);
        echo json_encode(["error" => "El usuario solicitado no existe."]);
    }
} catch (PDOException $e) {
    error_log('detallesUsuarioCompleto.php: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["error" => "Error al consultar el usuario."]);
}
