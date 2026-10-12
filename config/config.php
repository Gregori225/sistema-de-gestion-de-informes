<?php

/**
 * Configuración Centralizada de la Base de Datos
 * 
 * IMPORTANTE: En producción, usa variables de entorno (.env)
 * Ejemplo: $_ENV['DB_HOST'], $_ENV['DB_PASSWORD']
 */

// Configuración de la base de datos.
// En producción se recomienda definir estas variables de entorno (.env) y
// dejar los valores por defecto solo para desarrollo local.
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'INFORMESV1');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASSWORD', getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : 'viernes83'); // ⚠️ EN PRODUCCIÓN: Usar variables de entorno

// Única fuente de conexión: PDO a través de config/conexion.php
require_once __DIR__ . '/conexion.php';

// Se crea la conexión una sola vez y se exponen las variables que ya usan
// las vistas/acciones existentes ($conexion, $conexionPDO, $conexion_pg).
$conexion = conectar();

// Alias para compatibilidad con código existente
$conexionPDO = $conexion;

// Compatibilidad con código heredado que usa la extensión pg_* (login.php).
// Si la extensión no está instalada, simplemente no se crea y login.php
// usará PDO como vía principal.
$conexion_pg = null;
if (function_exists('pg_connect')) {
    $conexion_pg = @pg_connect(
        'host=' . DB_HOST .
        ' port=' . DB_PORT .
        ' dbname=' . DB_NAME .
        ' user=' . DB_USER .
        ' password=' . DB_PASSWORD
    );

    if ($conexion_pg) {
        pg_set_client_encoding($conexion_pg, 'UTF8');
    }
}
