<?php
session_start();
ob_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION["id"])) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
    exit();
}

require_once("../config/config.php");

$filtro = $_GET['filtro'] ?? 'semana';

try {
    if ($filtro === 'semana') {
        // Muestra los informes resueltos agrupados por día de los últimos 7 días
        $sql = "SELECT TO_CHAR(fecha, 'YYYY-MM-DD') AS etiqueta, COUNT(*) AS total
                FROM public.informes_tecnicos
                WHERE estado = 'Resuelto' AND fecha >= NOW() - INTERVAL '7 days'
                GROUP BY etiqueta
                ORDER BY etiqueta ASC";
    } else {
        // Muestra los informes resueltos agrupados por mes de los últimos 6 meses
        $sql = "SELECT TO_CHAR(fecha, 'YYYY-MM') AS etiqueta, COUNT(*) AS total
                FROM public.informes_tecnicos
                WHERE estado = 'Resuelto' AND fecha >= NOW() - INTERVAL '6 months'
                GROUP BY etiqueta
                ORDER BY etiqueta ASC";
    }

    $stmt = $conexion->query($sql);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $totals = [];

    foreach ($datos as $row) {
        $labels[] = $row['etiqueta'];
        $totals[] = (int)$row['total'];
    }

    ob_clean();
    echo json_encode([
        'status' => 'ok',
        'labels' => $labels,
        'totals' => $totals
    ]);
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
