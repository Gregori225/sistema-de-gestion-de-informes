<div class="table-responsive">
    <table class="table table-hover align-middle" id="table_usuarios">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Nombre</th>
                <th scope="col">Usuario (Login)</th>
                <th scope="col">Rol</th>
                <th scope="col">Cargo</th>
                <th scope="col">Departamento</th>
                <th scope="col">Estado</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Usamos la variable $usuarios que definimos en el index.php
            foreach ($usuarios as $usuario) { ?>
                <tr id="usuario_<?php echo $usuario['id']; ?>">
                    <th scope='row'><?php echo $usuario['id']; ?></th>
                    <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($usuario['rol']); ?></span></td>
                    <td><?php echo htmlspecialchars($usuario['cargo']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['departamento']); ?></td>
                    <td>
                        <?php if ($usuario['activo']): ?>
                            <span class="badge bg-success">Activo</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Nota: Mantenemos las funciones JS originales (verDetallesEmpleado, etc.) -->
                        <!-- para que sigan enlazando con los archivos .js del repositorio por ahora -->
                        <a title="Ver detalles del usuario" href="#" onclick="verDetallesEmpleado(<?php echo $usuario['id']; ?>)" class="btn btn-success btn-sm">
                            <i class="bi bi-binoculars"></i>
                        </a>
                        <a title="Editar datos del usuario" href="#" onclick="editarEmpleado(<?php echo $usuario['id']; ?>)" class="btn btn-warning btn-sm">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                        <a title="Eliminar datos del usuario" href="#" onclick="eliminarEmpleado(<?php echo $usuario['id']; ?>)" class="btn btn-danger btn-sm">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>