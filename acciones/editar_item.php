<?php
session_start();

// SEGURIDAD: Solo administradores
if (empty($_SESSION["id"]) || ($_SESSION["rol"] ?? '') !== 'admin') {
    header("location: ../login.php");
    exit();
}

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = filter_var($_POST["id"] ?? null, FILTER_VALIDATE_INT);
    $codigo_patrimonial = trim($_POST["codigo_patrimonial"] ?? '');
    $nombre = trim($_POST["nombre"] ?? '');
    $categoria = trim($_POST["categoria"] ?? '');
    $marca = trim($_POST["marca"] ?? null);
    $modelo = trim($_POST["modelo"] ?? null);
    $serial = trim($_POST["serial"] ?? null);
    $ubicacion_actual = trim($_POST["ubicacion_actual"] ?? null);
    $estado = trim($_POST["estado"] ?? 'Disponible');
    $observaciones = trim($_POST["observaciones"] ?? null);

    if (!$id || empty($codigo_patrimonial) || empty($nombre) || empty($categoria)) {
        header("location: ../inventario.php?error=campos_vacios");
        exit();
    }

    try {
        $sql = "UPDATE public.inventario 
                SET codigo_patrimonial = :codigo,
                    nombre = :nombre,
                    categoria = :categoria,
                    marca = :marca,
                    modelo = :modelo,
                    serial = :serial,
                    ubicacion_actual = :ubicacion,
                    estado = :estado,
                    observaciones = :observaciones
                WHERE id = :id";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo_patrimonial,
            ':nombre' => $nombre,
            ':categoria' => $categoria,
            ':marca' => !empty($marca) ? $marca : null,
            ':modelo' => !empty($modelo) ? $modelo : null,
            ':serial' => !empty($serial) ? $serial : null,
            ':ubicacion' => !empty($ubicacion_actual) ? $ubicacion_actual : null,
            ':estado' => $estado,
            ':observaciones' => !empty($observaciones) ? $observaciones : null,
            ':id' => $id
        ]);

        header("location: ../inventario.php?success=actualizado");
        exit();
    } catch (PDOException $e) {
        if ($e->getCode() == '23505') {
            header("location: ../inventario.php?error=codigo_duplicado");
            exit();
        }
        error_log("Error al actualizar item de inventario: " . $e->getMessage());
        header("location: ../inventario.php?error=db");
        exit();
    }
} else {
    header("location: ../inventario.php");
    exit();
}
