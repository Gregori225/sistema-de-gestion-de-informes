<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

require_once(__DIR__ . "/../config/config.php");

$user_id = $_SESSION["id"];
$notif_id = $_POST['id'] ?? null;

if (!$notif_id) {
    echo json_encode(['success' => false, 'error' => 'ID de notificación faltante']);
    exit;
}

try {
    $stmt = $conexion->prepare("UPDATE public.notificaciones 
                                SET leida = TRUE 
                                WHERE id = :id AND usuario_id = :user_id");
    $stmt->execute([':id' => $notif_id, ':user_id' => $user_id]);

    echo json_encode(['success' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    error_log("Error marcando notificación: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor']);
}
