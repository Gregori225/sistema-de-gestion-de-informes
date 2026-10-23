<?php
// acciones/delete.php

require_once __DIR__ . '/../includes/auth.php';

// SEGURIDAD: requiere sesión activa y rol de administrador para eliminar usuarios
requerirRol('admin');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once(__DIR__ . "/../config/config.php");

    // Leemos el cuerpo de la solicitud enviado por Axios
    $json_data = file_get_contents("php://input");
    $data = json_decode($json_data, true);

    // Verificamos si los datos se decodificaron correctamente
    if ($data !== null && isset($data['id'])) {
        $id = intval($data['id']); // Aseguramos que sea un número entero

        // No permitir que un administrador se elimine a sí mismo
        if ($id === intval($_SESSION['id'])) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(array("success" => false, "message" => "No puedes eliminar tu propio usuario."));
            exit;
        }

        try {
            // Apuntamos a tu tabla real en PostgreSQL
            $sql = "DELETE FROM public.usuarios WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                header('Content-Type: application/json');
                echo json_encode(array("success" => true, "message" => "Usuario eliminado correctamente"));
            } else {
                header('Content-Type: application/json');
                echo json_encode(array("success" => false, "message" => "No se pudo ejecutar la eliminación en la base de datos"));
            }
        } catch (PDOException $e) {
            // Captura errores por si el usuario está amarrado a otra tabla por clave foránea
            error_log('delete.php: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(array("success" => false, "message" => "Error al eliminar el usuario. Puede estar asociado a registros existentes."));
        }
    } else {
        header('Content-Type: application/json');
        echo json_encode(array("success" => false, "message" => "El parámetro 'id' no fue proporcionado o el JSON es inválido"));
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
}
