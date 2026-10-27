<?php

/**
 * Obtiene la cantidad de informes pendientes por leer de un usuario (en su buzón como técnico).
 */
function obtenerInformesPendientes($conexion, $userId)
{
    if (empty($userId)) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) FROM public.informes_tecnicos 
                WHERE id_tecnico = :user_id AND estado = 'Pendiente'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar informes pendientes: " . $e->getMessage());
        return 0;
    }
}

/**
 * Lista los informes recibidos por un usuario (es el técnico receptor).
 */
function obtenerInformesRecibidos($conexion, $userId)
{
    $sql = "SELECT i.*, u.nombre AS remitente_nombre, u.cargo AS remitente_cargo
            FROM public.informes_tecnicos i
            LEFT JOIN public.usuarios u ON i.id_solicitante = u.id
            WHERE i.id_tecnico = :user_id
            ORDER BY i.fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Lista los informes enviados por un usuario (es quien redactó/solicitó).
 */
function obtenerInformesEnviados($conexion, $userId)
{
    $sql = "SELECT i.*, u.nombre AS destinatario_nombre, u.cargo AS destinatario_cargo
            FROM public.informes_tecnicos i
            LEFT JOIN public.usuarios u ON i.id_tecnico = u.id
            WHERE i.id_solicitante = :user_id
            ORDER BY i.fecha DESC";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
