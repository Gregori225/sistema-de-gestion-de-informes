<?php
/**
 * Configuración Centralizada de la Base de Datos
 * 
 * IMPORTANTE: En producción, usa variables de entorno (.env)
 * Ejemplo: $_ENV['DB_HOST'], $_ENV['DB_PASSWORD']
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'INFORMESV1');
define('DB_USER', 'postgres');
define('DB_PASSWORD', 'viernes83'); // ⚠️ EN PRODUCCIÓN: Usar variables de entorno

// Conexión PDO (Recomendada para nuevas funcionalidades)
try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $conexion = new PDO($dsn, DB_USER, DB_PASSWORD);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Configurar encoding UTF-8
    $conexion->exec("SET NAMES 'UTF8'");
} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}

// Conexión clásica pg_connect (Para compatibilidad con login.php)
$conexion_pg = pg_connect(
    "host=" . DB_HOST . 
    " dbname=" . DB_NAME . 
    " user=" . DB_USER . 
    " password=" . DB_PASSWORD
);

if (!$conexion_pg) {
    die("Error de conexión PostgreSQL nativa: " . pg_last_error());
}

pg_set_client_encoding($conexion_pg, "UTF8");

// Alias para compatibilidad con código existente
$conexionPDO = $conexion;
