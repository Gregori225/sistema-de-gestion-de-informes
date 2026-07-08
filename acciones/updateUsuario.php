<?php
// acciones/updateUsuario.php

// 1. Incluimos la conexión a la base de datos Postgres
include_once("../config/config.php");

// Aseguramos que solo responda a peticiones POST de tu formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 2. Recolectamos los datos sanitizando entradas básicas
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : null;
    $rol = isset($_POST['rol']) ? trim($_POST['rol']) : null;
    $cargo = isset($_POST['cargo']) ? trim($_POST['cargo']) : null;
    $id_departamento = isset($_POST['id_departamento']) ? intval($_POST['id_departamento']) : null;

    // Postgres necesita un booleano nativo (true/false), no el string "true"/"false" de HTML
    $activo = isset($_POST['activo']) ? ($_POST['activo'] === 'true') : true;

    // Validación básica: que los campos requeridos no viajen vacíos
    if (!$id || !$nombre || !$usuario || !$rol || !$cargo || !$id_departamento) {
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Faltan campos obligatorios para actualizar."]);
        exit;
    }

    try {
        // 3. Preparamos la consulta SQL apuntando a tu esquema de Postgres
        $sql = "UPDATE public.usuarios 
                SET nombre = :nombre, 
                    usuario = :usuario, 
                    rol = :rol, 
                    cargo = :cargo, 
                    id_departamento = :id_departamento, 
                    activo = :activo 
                WHERE id = :id";

        $stmt = $conexion->prepare($sql);

        // 4. Vinculamos los parámetros asegurando sus tipos de datos correspondientes
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':rol', $rol, PDO::PARAM_STR);
        $stmt->bindParam(':cargo', $cargo, PDO::PARAM_STR);
        $stmt->bindParam(':id_departamento', $id_departamento, PDO::PARAM_INT);
        $stmt->bindParam(':activo', $activo, PDO::PARAM_BOOL); // Forzamos tipo booleano para Postgres
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // 5. Ejecutamos el cambio
        if ($stmt->execute()) {
            // Enviamos respuesta exitosa para que Axios (status 200) proceda con la alerta flotante
            header('Content-Type: application/json');
            echo json_encode(["status" => "success", "message" => "Usuario actualizado correctamente."]);
        } else {
            throw new Exception("No se pudo ejecutar la actualización del usuario.");
        }
    } catch (PDOException $e) {
        // Captura errores de base de datos (Ej: si violas una restricción UNIQUE de usuario)
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => "Error de base de datos: " . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    // Si intentan entrar directo desde la URL por GET, bloqueamos el acceso
    http_response_code(405); // Method Not Allowed
    echo "Método no permitido.";
}
