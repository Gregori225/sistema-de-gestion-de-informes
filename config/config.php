<?php

/**
 * Configuración Centralizada de la Base de Datos
 */

// Si existe la variable de entorno DATABASE_URL (la que te dio Neon / Render / Koyeb),
// la parseamos automáticamente para extraer todos los datos sin escribirlos a mano.
if (getenv('DATABASE_URL')) {
    $db_url = parse_url(getenv('DATABASE_URL'));
    define('DB_HOST', $db_url['host'] ?? 'localhost');
    define('DB_PORT', $db_url['port'] ?? '5432');
    define('DB_NAME', ltrim($db_url['path'] ?? '', '/'));
    define('DB_USER', $db_url['user'] ?? 'postgres');
    define('DB_PASSWORD', $db_url['pass'] ?? '');

    // Extraer parámetros extra como sslmode de la URL de Neon
    $queryParams = [];
    if (isset($db_url['query'])) {
        parse_str($db_url['query'], $queryParams);
    }
    define('DB_SSLMODE', $queryParams['sslmode'] ?? 'require');
} else {
    // Valores por defecto para desarrollo local (tu PC)
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_PORT', getenv('DB_PORT') ?: '5432');
    define('DB_NAME', getenv('DB_NAME') ?: 'INFORMESV1');
    define('DB_USER', getenv('DB_USER') ?: 'postgres');
    define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'viernes83');
    define('DB_SSLMODE', getenv('DB_SSLMODE') ?: 'prefer');
}

// Requerimos el archivo de conexión
require_once __DIR__ . '/conexion.php';

// Creamos la conexión principal
$conexion = conectar();
$conexionPDO = $conexion;

// Compatibilidad con código heredado que usa pg_* (login.php)
$conexion_pg = null;
if (function_exists('pg_connect')) {
    $ssl_str = (DB_SSLMODE === 'require') ? " sslmode=require" : "";
    $conexion_pg = @pg_connect(
        'host=' . DB_HOST .
            ' port=' . DB_PORT .
            ' dbname=' . DB_NAME .
            ' user=' . DB_USER .
            ' password=' . DB_PASSWORD .
            $ssl_str
    );

    if ($conexion_pg) {
        pg_set_client_encoding($conexion_pg, 'UTF8');
    }
}
