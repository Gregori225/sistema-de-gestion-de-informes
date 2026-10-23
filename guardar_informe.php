<?php
session_start();
require_once "config/config.php";

if (empty($_SESSION["id"])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data) {
    try {
        $sql = "INSERT INTO public.informes_tecnicos 
                (id_solicitante, id_tecnico, descripcion_equipo, diagnostico_problema, observacion, estado) 
                VALUES (:id_solicitante, :id_tecnico, :descripcion, :diagnostico, :observacion, 'Pendiente') 
                RETURNING id";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_solicitante' => intval($data['id_solicitante']),
            ':id_tecnico'     => intval($_SESSION["id"]),
            ':descripcion'    => $data['descripcion_equipo'],
            ':diagnostico'    => $data['diagnostico_problema'],
            ':observacion'    => $data['observacion']
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['status' => 'ok', 'id' => $row['id']]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Sin datos']);
}
