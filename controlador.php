<?php
/**
 * Controlador de autenticación (login).
 *
 * Procesador: login.php
 * Dependencias: includes/auth.php, config/config.php ($conexion PDO)
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/config.php';

// Función para verificar sesión activa
if (!function_exists('verificarSesion')) {
    function verificarSesion()
    {
        if (empty($_SESSION['id'])) {
            header('location: login.php');
            exit();
        }
    }
}

$error = null;

if (isset($_POST['btningresar'])) {
    $usuario    = trim($_POST['usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? '';

    if ($usuario === '' || $contrasena === '') {
        $error = 'Por favor, completa todos los campos';
    } else {
        try {
            // Consulta parametrizada: evita inyección SQL.
            $stmt = $conexion->prepare(
                'SELECT id, usuario, contrasena, nombre, rol, cargo, id_departamento
                 FROM public.usuarios
                 WHERE usuario = :usuario AND activo = TRUE
                 LIMIT 1'
            );
            $stmt->execute([':usuario' => $usuario]);
            $datos = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificación de contraseña con soporte dual:
            //  - Hash bcrypt/argon2 (recomendado, columna contrasena).
            //  - Texto plano heredado (se migra automáticamente a hash al primer login).
            $almacenada = (string) ($datos['contrasena'] ?? '');
            $esHash = str_starts_with($almacenada, '$2y$') || str_starts_with($almacenada, '$argon2');

            $passwordOk = false;
            if ($datos) {
                if ($esHash) {
                    $passwordOk = password_verify($contrasena, $almacenada);
                } elseif (hash_equals($almacenada, $contrasena)) {
                    $passwordOk = true;
                    // Migración transparente a hash (best-effort).
                    try {
                        $nuevoHash = password_hash($contrasena, PASSWORD_DEFAULT);
                        $upd = $conexion->prepare('UPDATE public.usuarios SET contrasena = :hash WHERE id = :id');
                        $upd->execute([':hash' => $nuevoHash, ':id' => $datos['id']]);
                    } catch (PDOException $e) {
                        error_log('No se pudo migrar la contraseña a hash: ' . $e->getMessage());
                    }
                }
            }

            if ($datos && $passwordOk) {
                // Prevención de fijación de sesión (session fixation).
                session_regenerate_id(true);

                $_SESSION['id']               = $datos['id'];
                $_SESSION['usuario']          = $datos['usuario'];
                $_SESSION['nombre']           = $datos['nombre'];
                $_SESSION['rol']              = $datos['rol'];
                $_SESSION['cargo']            = $datos['cargo'];
                $_SESSION['id_departamento']  = $datos['id_departamento'];

                header('Location: dashboard.php');
                exit();
            } elseif ($datos) {
                $error = 'Contraseña incorrecta';
            } else {
                $error = 'Usuario no encontrado o inactivo';
            }
        } catch (PDOException $e) {
            error_log('Error en autenticación: ' . $e->getMessage());
            $error = 'Ocurrió un error al intentar iniciar sesión. Intente de nuevo.';
        }
    }
}
