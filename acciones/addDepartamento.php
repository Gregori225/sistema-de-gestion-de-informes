<?php

/**
 * Registra un nuevo departamento (POST JSON).
 */

require_once __DIR__ . '/../includes/auth.php';
requerirSesion();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido."]);
    exit;
}

// Verificación CSRF (la página que inicia la petición provee el token en la cabecera)
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    http_response_code(403);
    echo json_encode(["error" => "Token CSRF inválido o ausente."]);
    exit;
}

require_once("../config/config.php");

$data   = json_decode(file_get_contents("php://input"), true);
$nombre = trim($data['nombre'] ?? '');

if ($nombre === '' || mb_strlen($nombre) > 100) {
    http_response_code(400);
    echo json_encode(["error" => "El nombre del departamento es requerido (máx. 100 caracteres)."]);
    exit;
}

try {
    $sql = "INSERT INTO public.departamentos (nombre) VALUES (:nombre) RETURNING id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':nombre' => $nombre]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "id"      => $resultado['id'],
        "nombre"  => $nombre
    ]);
} catch (PDOException $e) {
    // 23505 = violación de UNIQUE (nombre duplicado)
    if ($e->getCode() === '23505') {
        http_response_code(409);
        echo json_encode(["error" => "Ya existe un departamento con ese nombre."]);
    } else {
        error_log("Error al crear departamento: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(["error" => "Error interno en el servidor."]);
    }
}
