<?php
session_start();

// SEGURIDAD Y RESTRICCIÓN DE ROL
if (empty($_SESSION["id"]) || ($_SESSION["rol"] ?? '') !== 'admin') {
    header("location: ../login.php");
    exit();
}

require_once("../config/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $codigo_patrimonial = trim($_POST["codigo_patrimonial"] ?? '');
    $nombre = trim($_POST["nombre"] ?? '');
    $categoria = trim($_POST["categoria"] ?? '');
    $marca = trim($_POST["marca"] ?? null);
    $modelo = trim($_POST["modelo"] ?? null);
    $serial = trim($_POST["serial"] ?? null);
    $ubicacion_actual = trim($_POST["ubicacion_actual"] ?? null);
    $estado = trim($_POST["estado"] ?? 'Disponible');
    $observaciones = trim($_POST["observaciones"] ?? null);

    // Validación básica de campos obligatorios
    if (empty($codigo_patrimonial) || empty($nombre) || empty($categoria)) {
        header("location: ../inventario.php?error=campos_vacios");
        exit();
    }

    try {
        $sql = "INSERT INTO public.inventario (codigo_patrimonial, nombre, categoria, marca, modelo, serial, ubicacion_actual, estado, observaciones) 
                VALUES (:codigo, :nombre, :categoria, :marca, :modelo, :serial, :ubicacion, :estado, :observaciones)";

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
            ':observaciones' => !empty($observaciones) ? $observaciones : null
        ]);

        header("location: ../inventario.php?success=registrado");
        exit();
    } catch (PDOException $e) {
        // Manejar error si el código de bien ya existe (violación de índice único)
        if ($e->getCode() == '23505') {
            header("location: ../inventario.php?error=codigo_duplicado");
            exit();
        }
        error_log("Error al guardar item en inventario: " . $e->getMessage());
        header("location: ../inventario.php?error=db");
        exit();
    }
} else {
    header("location: ../inventario.php");
    exit();
}
