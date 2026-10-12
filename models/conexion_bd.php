<?php
// 1. Conexión Clásica NATIVA (Para que tu login NO se rompa)
$conexion = pg_connect("host=localhost dbname=INFORMESV1 user=postgres password=viernes83");
pg_set_client_encoding($conexion, "UTF8");

// 2. Conexión Moderna PDO (Para que tu nuevo CRUD funcione a la perfección)
try {
    $conexionPDO = new PDO("pgsql:host=localhost;port=5432;dbname=INFORMESV1", "postgres", "viernes83");
    $conexionPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en el puente de conexión PDO: " . $e->getMessage());
}
?>