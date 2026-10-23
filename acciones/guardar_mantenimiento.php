<?php
session_start();

// SEGURIDAD: Solo administradores
if (empty($_SESSION["id"]) || ($_SESSION["rol"] ?? '') !== 'admin') {
    header("location: ../login.php");
    exit();
}

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_item = filter_var($_POST["id_item"] ?? null, FILTER_VALIDATE_INT);
    $tipo_mantenimiento = trim($_POST["tipo_mantenimiento"] ?? '');
    $id_tecnico = filter_var($_POST["id_tecnico"] ?? null, FILTER_VALIDATE_INT);
    $descripcion_trabajo = trim($_POST["descripcion_trabajo"] ?? '');

    $fecha_raw = trim($_POST["fecha_mantenimiento"] ?? '');

    // Limpiar y formatear la fecha del input datetime-local
    if (!empty($fecha_raw)) {
        $fecha_mantenimiento = str_replace('T', ' ', $fecha_raw);
        if (strlen($fecha_mantenimiento) === 16) {
            $fecha_mantenimiento .= ':00';
        }
    } else {
        $fecha_mantenimiento = date('Y-m-d H:i:s');
    }

    // Validar campos obligatorios
    if (!$id_item || empty($tipo_mantenimiento) || !$id_tecnico || empty($descripcion_trabajo)) {
        header("location: ../inventario.php?error=campos_vacios");
        exit();
    }

    try {
        // Insertar el mantenimiento en el historial
        $sql = "INSERT INTO public.mantenimientos_inventario (id_item, id_tecnico, tipo_mantenimiento, descripcion_trabajo, fecha_mantenimiento) 
                VALUES (:id_item, :id_tecnico, :tipo_mantenimiento, :descripcion_trabajo, :fecha_mantenimiento)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':id_item' => $id_item,
            ':id_tecnico' => $id_tecnico,
            ':tipo_mantenimiento' => $tipo_mantenimiento,
            ':descripcion_trabajo' => $descripcion_trabajo,
            ':fecha_mantenimiento' => $fecha_mantenimiento
        ]);

        // (Se eliminó la consulta UPDATE que alteraba el estado del equipo)

        header("location: ../inventario.php?success=mantenimiento_registrado");
        exit();
    } catch (PDOException $e) {
        error_log("Error al registrar mantenimiento: " . $e->getMessage());
        header("location: ../inventario.php?error=db");
        exit();
    }
} else {
    header("location: ../inventario.php");
    exit();
}
