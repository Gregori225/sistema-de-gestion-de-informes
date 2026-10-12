<?php
require_once("../config/config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $nombre = trim($data['nombre'] ?? '');

    if (empty($nombre)) {
        http_response_code(400);
        echo json_encode(["error" => "El nombre del departamento es requerido."]);
        exit;
    }

    try {
        $sql = "INSERT INTO public.departamentos (nombre) VALUES (:nombre) RETURNING id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':nombre' => $nombre]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            "success" => true,
            "id" => $resultado['id'],
            "nombre" => $nombre
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["error" => "Error interno en el servidor: " . $e->getMessage()]);
    }
}
