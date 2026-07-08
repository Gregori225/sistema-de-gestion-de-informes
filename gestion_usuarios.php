<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>CRUD de Usuarios en PHP, PostgreSQL utilizando MODALES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="css/home.css">
</head>

<body>
  <?php
  // Asegúrate de que config.php tenga tu conexión PDO a Postgres que reparamos antes
  include("models/conexion_bd.php");
  include("acciones/acciones.php");

  // Módulo de Usuarios 
  $usuarios = obtenerUsuarios($conexionPDO);
  $totalUsuarios = $usuarios->rowCount();

  // Departamentos
  $departamentos = obtenerDepartamentos($conexionPDO);
  $totalDepartamentos = $departamentos->rowCount();
  ?>

  <h1 class="text-center mt-5 mb-2 fw-bold">CRUD completo de Usuarios con PHP, PostgreSQL y Bootstrap 5</h1>

  <main class="container my-5">
    <div class="row justify-content-center">
      <div class="col-xl-10">

        <!-- 👥 SECCIÓN DE USUARIOS -->
        <section id="modulo-usuarios" class="mb-5" aria-labelledby="titulo-usuarios">
          <header class="d-flex justify-content-between align-items-center mb-4">
            <h1 id="titulo-usuarios" class="h2 mb-0 text-dark">
              Lista de Usuarios <span class="badge bg-secondary fs-6"><?php echo $totalUsuarios; ?></span>
            </h1>
            <div class="header-actions">
              <button onclick="modalRegistrarEmpleado()" class="btn btn-success me-2" title="Registrar Nuevo Usuario">
                <i class="bi bi-person-plus"></i> <span class="d-none d-sm-inline">Nuevo Usuario</span>
              </button>
              <a href="acciones/exportar.php" class="btn btn-outline-success" title="Exportar a CSV">
                <i class="bi bi-filetype-csv"></i>
              </a>
            </div>
          </header>

          <!-- Filtro de Búsqueda -->
          <div class="row mb-3 justify-content-end">
            <div class="col-md-5 col-lg-4">
              <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar usuarios...">
              </div>
            </div>
          </div>

          <!-- Contenedor con Scroll de Usuarios -->
          <div class="table-responsive bg-white border rounded shadow-sm" style="max-height: 380px; overflow-y: auto;">
            <?php include("models/usuarios.php"); ?>
          </div>
        </section>

        <!-- ↕️ SEPARADOR VISUAL -->
        <hr class="my-5" style="border-top: 2px dashed #dee2e6;">

        <!-- 🏢 SECCIÓN DE DEPARTAMENTOS -->
        <section id="modulo-departamentos" class="mb-4" aria-labelledby="titulo-departamentos">
          <header class="d-flex justify-content-between align-items-center mb-4">
            <h2 id="titulo-departamentos" class="h3 mb-0 text-dark">
              Lista de Departamentos <span class="badge bg-primary fs-6"><?php echo $totalDepartamentos; ?></span>
            </h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartamentoModal" title="Registrar Nuevo Departamento">
              <i class="bi bi-folder-plus"></i> <span class="d-none d-sm-inline">Nuevo Departamento</span>
            </button>
          </header>

          <!-- Contenedor con Scroll de Departamentos -->
          <div class="table-responsive bg-white border rounded shadow-sm" style="max-height: 280px; overflow-y: auto;">
            <table class="table table-hover align-middle mb-0" id="tablaDepartamentos">
              <thead class="table-light style-sticky-top" style="position: sticky; top: 0; z-index: 2;">
                <tr>
                  <th scope="col" style="width: 15%;">ID</th>
                  <th scope="col" style="width: 65%;">Nombre del Departamento</th>
                  <th scope="col" style="width: 20%;" class="text-center">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($departamentos as $dep): ?>
                  <tr id="dep-<?php echo $dep['id']; ?>">
                    <td><strong><?php echo $dep['id']; ?></strong></td>
                    <td><?php echo htmlspecialchars($dep['nombre']); ?></td>
                    <td class="text-center">
                      <button class="btn btn-outline-danger btn-sm" onclick="eliminarDepartamento(<?php echo $dep['id']; ?>)" title="Eliminar Departamento">
                        <i class="bi bi-trash"></i>
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>

      </div>
    </div>
  </main>

  <!-- 🗔 MODAL SEMÁNTICO PARA AGREGAR DEPARTAMENTO -->
  <div class="modal fade" id="addDepartamentoModal" tabindex="-1" aria-labelledby="modalDepTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalDepTitulo"><i class="bi bi-folder-plus"></i> Crear Departamento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar modal"></button>
        </div>
        <div class="modal-body p-4">
          <form id="formDepartamento" onsubmit="event.preventDefault(); guardarDepartamento();">
            <div class="mb-3">
              <label for="nombreDep" class="form-label fw-semibold">Nombre del Departamento</label>
              <input type="text" class="form-control form-control-lg" id="nombreDep" required placeholder="Ej. Tecnología, Talento Humano" autocomplete="off">
            </div>
          </form>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary px-4" onclick="guardarDepartamento()">Guardar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Librerías de Terceros e Infraestructura -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
  <script src="https://unpkg.com/nextjs-toast-notify@latest/dist/nextjs-toast-notify.min.js"></script>

  <!-- Scripts Propios del Sistema (Módulo Usuarios) -->
  <script src="js/detallesEmpleado.js"></script>
  <script src="js/addEmpleado.js"></script>
  <script src="js/editarEmpleado.js"></script>
  <script src="js/eliminarEmpleado.js"></script>
  <script src="js/refreshTableAdd.js"></script>
  <script src="js/refreshTableEdit.js"></script>
  <script src="js/buscarUsuario.js"></script>

  <!-- Scripts Propios del Sistema (Módulo Departamentos) -->
  <script src="js/departamentos.js"></script>

</body>

</html>