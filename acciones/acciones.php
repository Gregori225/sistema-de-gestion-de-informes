<?php
/**
 * Acciones del módulo de gestión de usuarios.
 *
 * - Procesador POST: crea usuarios (usado por js/addEmpleado.js vía FormData).
 * - Funciones de apoyo usadas por gestion_usuarios.php.
 */

require_once __DIR__ . '/../includes/auth.php';
require_once(__DIR__ . "/../config/config.php");

/**
 * Devuelve (y crea si no existe) el token CSRF de la sesión actual.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/* ---------------------------------------------------------------------------
 * Protección CSRF para los endpoints POST de este archivo.
 * ------------------------------------------------------------------------- */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    requerirSesion();

    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        echo json_encode(["success" => false, "message" => "Token CSRF inválido o ausente."]);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos los campos reales de la tabla public.usuarios
    $usuario         = trim($_POST['usuario'] ?? '');
    $contrasena      = trim($_POST['contrasena'] ?? '');
    $nombre          = trim($_POST['nombre'] ?? '');
    $rol             = trim($_POST['rol'] ?? '');
    $cargo           = trim($_POST['cargo'] ?? '');
    $id_departamento = intval($_POST['id_departamento'] ?? 0);

    // Validación de campos obligatorios
    if ($usuario === '' || $contrasena === '' || $nombre === '' || $rol === '' || $cargo === '' || $id_departamento <= 0) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Faltan campos obligatorios."]);
        exit;
    }

    // El rol debe coincidir con el ENUM 'rol_usuario' de PostgreSQL
    $rolesValidos = ['admin', 'user'];
    if (!in_array($rol, $rolesValidos, true)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Rol de usuario no válido."]);
        exit;
    }

    try {
        // Almacenamos la contraseña como hash seguro (bcrypt).
        // La columna real según Structure.sql es 'contrasena' (no 'contrasena_hash').
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);

        $sql = "INSERT INTO public.usuarios (usuario, contrasena, nombre, rol, cargo, id_departamento)
                VALUES (:usuario, :contrasena, :nombre, :rol, :cargo, :id_departamento)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':usuario'          => $usuario,
            ':contrasena'       => $hash,
            ':nombre'           => $nombre,
            ':rol'              => $rol,
            ':cargo'            => $cargo,
            ':id_departamento'  => $id_departamento,
        ]);

        header('Content-Type: application/json');
        echo json_encode(["success" => true, "message" => "Usuario registrado correctamente."]);
        exit;
    } catch (PDOException $e) {
        // 23505 = violación de UNIQUE (usuario duplicado)
        if ($e->getCode() === '23505') {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "El nombre de usuario ya existe."]);
        } else {
            error_log("Error al crear usuario: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(["success" => false, "message" => "Error al crear el registro."]);
        }
        exit;
    }
}

/**
 * Función para obtener todos los usuarios con su departamento
 */
function obtenerUsuarios($conexion)
{
    // Usamos un JOIN para traer el nombre del departamento y no solo el número de ID
    $sql = "SELECT u.id, u.usuario, u.nombre, u.rol, u.cargo, u.activo, d.nombre AS departamento
            FROM public.usuarios u
            INNER JOIN public.departamentos d ON u.id_departamento = d.id
            ORDER BY u.id ASC";

    $stmt = $conexion->query($sql);
    if (!$stmt) {
        return false;
    }
    return $stmt;
}


function obtenerDepartamentos($conexion)
{
    $sql = "SELECT id, nombre FROM public.departamentos ORDER BY id ASC";
    return $conexion->query($sql);
}
