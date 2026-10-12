<?php

/**
 * Obtiene la cantidad de informes pendientes por leer de un usuario.
 */
function obtenerInformesPendientes($conexion, $userId)
{
    if (empty($userId)) {
        return 0;
    }

    try {
        $sql = "SELECT COUNT(*) FROM public.informes_tecnicos 
                WHERE id_solicitante = :user_id AND estado = 'Pendiente'";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error al contar informes pendientes: " . $e->getMessage());
        return 0;
    }
}
