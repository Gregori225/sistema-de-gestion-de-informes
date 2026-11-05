<?php

/**
 * Conexión única a la base de datos (PDO) con soporte para Neon y SSL.
 */

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

        // Tomamos los valores definidos en config.php (o respaldos si se llama aislado)
        $host = defined('DB_HOST') ? DB_HOST : (getenv('DB_HOST') ?: 'localhost');
        $port = defined('DB_PORT') ? DB_PORT : (getenv('DB_PORT') ?: '5432');
        $name = defined('DB_NAME') ? DB_NAME : (getenv('DB_NAME') ?: 'INFORMESV1');
        $user = defined('DB_USER') ? DB_USER : (getenv('DB_USER') ?: 'postgres');
        $pass = defined('DB_PASSWORD') ? DB_PASSWORD : '';
        $sslmode = defined('DB_SSLMODE') ? DB_SSLMODE : 'require';

        try {
            // DSN incluyendo obligatoriamente el sslmode requerido por Neon
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s;sslmode=%s', $host, $port, $name, $sslmode);

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
