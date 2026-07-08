<?php
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    require_once("../config/config.php");

    // Obtener el ID de usuario de la solicitud GET y asegurarse de que sea un entero
    $IdUsuario = (int)$_GET['id'];

    // Realizar la consulta para obtener los detalles del usuario con el ID proporcionado
    $sql = "SELECT * FROM public.usuarios WHERE id = :id LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':id', $IdUsuario, PDO::PARAM_INT);
    $stmt->execute();

    // Verificar si la consulta se ejecutó correctamente
    if (!$stmt) {
        // Manejar el error aquí si la consulta no se ejecuta correctamente
        echo json_encode(["error" => "Error al obtener los detalles del usuario: " . implode(", ", $conexion->errorInfo())]);
        exit();
    }

    // Obtener los detalles del usuario como un array asociativo
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los detalles del usuario como un objeto JSON
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($usuario);
    exit;
}
