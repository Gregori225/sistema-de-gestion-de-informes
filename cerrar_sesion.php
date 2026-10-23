<?php
/**
 * Cierre de sesión seguro: limpia los datos y destruye la sesión.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

// Invalidar la cookie de sesión del navegador.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy(); // Elimina toda la información de la sesión actual

header('Location: login.php'); // Nos manda de vuelta al login
exit();
