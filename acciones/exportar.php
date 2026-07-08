<?php
// acciones/exportarCSV.php
include("../config/config.php");

$fecha_actual = date("Y-m-d");
$filename = "usuarios_" . $fecha_actual . ".csv";

// 1. CONFIGURAR CABECERAS PRIMERO (Evita el error 'Headers already sent')
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// 2. NUEVA CONSULTA SQL (Adaptada a Postgres y con los nombres reales de departamentos)
$sql = "SELECT u.id, u.nombre, u.usuario, u.rol, u.cargo, d.nombre AS departamento, 
               CASE WHEN u.activo THEN 'Activo' ELSE 'Inactivo' END AS estado
        FROM public.usuarios u
        INNER JOIN public.departamentos d ON u.id_departamento = d.id
        ORDER BY u.id ASC";

try {
    $stmt = $conexion->query($sql);

    // Verificar si hay datos obtenidos de la consulta
    if ($stmt->rowCount() > 0) {
        // Abrir el flujo para escritura
        $fp = fopen('php://output', 'w');

        // TRUCO PARA EXCEL: Forzamos el mapa de caracteres UTF-8 (BOM)
        // Esto hace que Excel reconozca perfectamente los acentos de tu tabla
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados actualizados basados en tu tabla HTML
        $fields = array('ID', 'Nombre', 'Usuario (Login)', 'Rol', 'Cargo', 'Departamento', 'Estado');

        // Agregamos los encabezados usando punto y coma (;) como separador
        fputcsv($fp, $fields, ';');

        // Iterar sobre los resultados de Postgres y agregar cada fila al CSV
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($fp, $row, ';');
        }

        // Cerrar el flujo
        fclose($fp);
        exit();
    } else {
        // Si por alguna razón entra aquí, limpiamos las cabeceras de descarga para mostrar el texto
        header_remove();
        echo "No hay usuarios registrados para generar el reporte.";
    }
} catch (PDOException $e) {
    header_remove();
    echo "Error al conectar con PostgreSQL: " . $e->getMessage();
}
