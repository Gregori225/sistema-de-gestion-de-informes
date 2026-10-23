<?php
session_start();

// SEGURIDAD: Solo administradores
if (empty($_SESSION["id"]) || ($_SESSION["rol"] ?? '') !== 'admin') {
    header("location: ../login.php");
    exit();
}

require_once("../config/config.php");

$id = filter_var($_GET["id"] ?? null, FILTER_VALIDATE_INT);

if (!$id) {
    header("location: ../inventario.php?error=id_invalido");
    exit();
}

try {
    $sql = "DELETE FROM public.inventario WHERE id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':id' => $id]);

    header("location: ../inventario.php?success=eliminado");
    exit();
} catch (PDOException $e) {
    error_log("Error al eliminar item de inventario: " . $e->getMessage());
    header("location: ../inventario.php?error=db");
    exit();
}
