<?php
// Conexión a la base de datos para cargar dinámicamente los departamentos existentes
require_once(__DIR__ . "/../config/config.php");

$departamentos = [];
try {
    $queryDep = "SELECT id, nombre FROM public.departamentos ORDER BY nombre ASC";
    $stmtDep = $conexion->query($queryDep);
    if ($stmtDep) {
        $departamentos = $stmtDep->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error_dep = $e->getMessage();
}
?>

<div class="modal fade" id="editarUsuarioModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 titulo_modal">Actualizar Información del Usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <?php if (isset($error_dep)): ?>
                <div class="alert alert-danger m-2">Error al cargar departamentos: <?php echo $error_dep; ?></div>
            <?php endif; ?>

            <div class="modal-body">
                <form id="formularioUsuarioEdit" action="" method="POST" autocomplete="off">

                    <!-- ID oculto para saber a qué registro de Postgres aplicarle el UPDATE -->
                    <input type="hidden" name="id" id="id_usuario" />

                    <!-- Campo: Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" id="nombre_edit" class="form-control" required />
                    </div>

                    <!-- Campo: Usuario (Login) -->
                    <div class="mb-3">
                        <label class="form-label">Usuario (Login)</label>
                        <input type="text" name="usuario" id="usuario_edit" class="form-control" required />
                    </div>

                    <div class="row mb-3">
                        <!-- Campo: Rol (Administrador / Tecnico / Usuario) -->
                        <div class="col-md-6">
                            <label class="form-label">Rol de Usuario</label>
                            <select name="rol" id="rol_edit" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="admin">Administrador</option>
                                <option value="user">Usuario</option>
                            </select>
                        </div>

                        <!-- Campo: Cargo -->
                        <div class="col-md-6">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" id="cargo_edit" class="form-control" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Campo: Departamento (Dinamico) -->
                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <select name="id_departamento" id="id_departamento_edit" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($departamentos as $dep): ?>
                                    <option value="<?php echo $dep['id']; ?>">
                                        <?php echo htmlspecialchars($dep['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Campo: Estado (Activo/Inactivo) -->
                        <div class="col-md-6">
                            <label class="form-label">Estado en el Sistema</label>
                            <select name="activo" id="activo_edit" class="form-select" required>
                                <option value="true">Activo</option>
                                <option value="false">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <!-- Mantenemos la ejecución de tu función original en el JS -->
                        <button type="submit" class="btn btn-warning btn_add text-white" onclick="actualizarUsuario(event)">
                            Actualizar datos del usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>