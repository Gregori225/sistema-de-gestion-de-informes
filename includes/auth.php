<?php
/**
 * Helpers centralizados de sesión y autorización.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * ¿Hay un usuario autenticado?
 */
function estaAutenticado(): bool
{
    return !empty($_SESSION['id']);
}

/**
 * Exige sesión activa; si no existe, redirige al login.
 */
function requerirSesion(): void
{
    if (!estaAutenticado()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Exige un rol concreto (además de sesión activa).
 *
 * @param string $rol Rol permitido, p. ej. 'Administrador'.
 */
function requerirRol(string $rol): void
{
    requerirSesion();
    if (($_SESSION['rol'] ?? '') !== $rol) {
        http_response_code(403);
        exit('Acceso denegado: se requiere el rol ' . htmlspecialchars($rol) . '.');
    }
}
