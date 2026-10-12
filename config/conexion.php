<?php
/**
 * Conexión única a la base de datos (PDO) con soporte para variables de entorno.
 *
 * Lee config/config.php (constantes DB_*) y expone la función conectar() que
 * devuelve una instancia PDO compartida (singleton).
 */

require_once __DIR__ . '/config.php';

if (!function_exists('conectar')) {
    /**
     * Devuelve la conexión PDO (se crea una sola vez por petición).
     *
     * @return PDO
     */
    function conectar(): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        // Prioridad: variables de entorno (.env / php.ini) > constantes de config.php
        $host = getenv('DB_HOST') ?: DB_HOST;
        $port = getenv('DB_PORT') ?: DB_PORT;
        $name = getenv('DB_NAME') ?: DB_NAME;
        $user = getenv('DB_USER') ?: DB_USER;
        $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : DB_PASSWORD;

        try {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $name);
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $pdo->exec("SET NAMES 'UTF8'");
        } catch (PDOException $e) {
            error_log('Error de conexión a la base de datos: ' . $e->getMessage());
            http_response_code(500);
            exit('Error de conexión a la base de datos. Intente más tarde.');
        }

        return $pdo;
    }
}
