<?php
// Incluimos la configuración para conectarnos a Postgres y traer los departamentos
// Usamos ../ porque este archivo vive dentro de la carpeta 'modales'
require_once("../config/config.php");

$departamentos = [];
try {
    $queryDep = "SELECT id, nombre FROM public.departamentos ORDER BY nombre ASC";
    $stmtDep = $conexion->query($queryDep);
    if ($stmtDep) {
        $departamentos = $stmtDep->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Si hay un error, lo ideal es dejar el arreglo vacío para que no rompa el HTML
    $error_dep = $e->getMessage();
}
?>

<div class="modal fade" id="agregarEmpleadoModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 titulo_modal">Registrar Nuevo Usuario</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-content-error">
                <?php if (isset($error_dep)): ?>
                    <div class="alert alert-danger m-2">Error al cargar departamentos: <?php echo $error_dep; ?></div>
                <?php endif; ?>
            </div>
            <div class="modal-body">
                <!-- Mantenemos el ID "formularioEmpleado" para que el JS original lo procese sin romperse -->
                <form id="formularioEmpleado" action="" method="POST" autocomplete="off">

                    <!-- Campo: Nombre -->
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre" class="form-control" required placeholder="Ej. Juan Pérez" />
                    </div>

                    <div class="row mb-3">
                        <!-- Campo: Usuario (Login) -->
                        <div class="col-md-6">
                            <label class="form-label">Usuario (Login)</label>
                            <input type="text" name="usuario" class="form-control" required placeholder="jperez" />
                        </div>
                        <!-- Campo: Contraseña -->
                        <div class="col-md-6">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" required placeholder="********" />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <!-- Campo: Rol (Enum) -->
                        <div class="col-md-6">
                            <label class="form-label">Rol de Usuario</label>
                            <select name="rol" class="form-select" required>
                                <option value="" selected>Seleccione</option>
                                <!-- Valores actualizados para coincidir con el ENUM 'rol_usuario' en PostgreSQL -->
                                <option value="admin">Administrador</option>
                                <option value="user">Usuario</option>
                            </select>
                        </div>
                        <!-- Campo: Cargo -->
                        <div class="col-md-6">
                            <label class="form-label">Cargo</label>
                            <input type="text" name="cargo" class="form-control" required placeholder="Ej. Analista de Soporte" />
                        </div>
                    </div>

                    <!-- Campo: Departamento (Select Dinámico) -->
                    <div class="mb-3">
                        <label class="form-label">Departamento</label>
                        <select name="id_departamento" class="form-select" required>
                            <option selected value="">Seleccione el departamento</option>
                            <?php foreach ($departamentos as $dep): ?>
                                <option value="<?php echo $dep['id']; ?>">
                                    <?php echo htmlspecialchars($dep['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <!-- Mantenemos la función registrarEmpleado(event) conectada a tu JS -->
                        <button type="submit" class="btn btn-primary btn_add" onclick="registrarEmpleado(event)">
                            Registrar nuevo usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>