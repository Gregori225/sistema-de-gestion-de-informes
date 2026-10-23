<?php
session_start();
header('Content-Type: application/json');

if (empty($_SESSION["id"])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

require_once("../config/config.php");

$data = json_decode(file_get_contents("php://input"), true);
$id_informe = intval($data['id'] ?? 0);
$nuevo_estado = $data['estado'] ?? '';

// Validar que el estado sea uno de los permitidos por el ENUM
$estados_validos = ['Pendiente', 'Leido', 'En Proceso', 'Resuelto', 'Cancelado'];

if ($id_informe <= 0 || !in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos.']);
    exit();
}

try {
    $sql = "UPDATE public.informes_tecnicos SET estado = :estado WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':estado' => $nuevo_estado,
        ':id' => $id_informe
    ]);

    echo json_encode(['status' => 'ok']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
