<?php
// 1. FORZAR MOSTRAR ERRORES (SOLO PARA DEPURAR)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION["id"])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once(__DIR__ . "/../config/config.php");

if (!isset($conexion)) {
    die("ERROR CRÍTICO: La variable \$conexion no existe. Revisa tu config.php");
}

$user_id = $_SESSION["id"];

try {
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // NUEVO: Verificar si solo se está pidiendo el contador para el auto-refresh
    if (isset($_GET['solo_contador']) && $_GET['solo_contador'] == '1') {
        $stmtCount = $conexion->prepare("SELECT COUNT(*) FROM public.notificaciones WHERE usuario_id = :user_id AND leida = FALSE");
        $stmtCount->execute([':user_id' => $user_id]);
        $no_leidas = (int)$stmtCount->fetchColumn();

        echo json_encode(['no_leidas' => $no_leidas]);
        exit;
    }

    // Comportamiento normal: Devolver las últimas 5 notificaciones
    $stmt = $conexion->prepare("SELECT id, titulo, mensaje, tipo, fecha_creacion, leida 
                                FROM public.notificaciones 
                                WHERE usuario_id = :user_id 
                                ORDER BY fecha_creacion DESC 
                                LIMIT 5");
    $stmt->execute([':user_id' => $user_id]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['notificaciones' => $notificaciones]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Fallo en Base de Datos',
        'detalle' => $e->getMessage()
    ]);
}
