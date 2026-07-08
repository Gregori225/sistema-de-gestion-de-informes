// Define la función globalmente adjuntándola al objeto window
window.insertarUsuarioTabla = async function () {
  try {
    // Apuntamos al nuevo archivo PHP que traerá el último usuario de Postgres
    const response = await axios.get(`acciones/getUltimoUsuario.php`);

    if (response.status === 200) {
      const infoUsuario = response.data; // Obtener los datos del usuario

      // Apuntamos al ID de tu nueva tabla (#table_usuarios)
      let tableBody = document.querySelector("#table_usuarios tbody");

      let tr = document.createElement("tr");
      tr.id = `usuario_${infoUsuario.id}`; // ID adaptado a usuario

      // Armamos las celdas con tus columnas reales de PostgreSQL
      tr.innerHTML = `
        <th scope="row">${infoUsuario.id}</th>
        <td>${infoUsuario.nombre}</td>
        <td>${infoUsuario.usuario}</td>
        <td><span class="badge bg-info text-dark">${infoUsuario.rol}</span></td>
        <td>${infoUsuario.cargo}</td>
        <td>${infoUsuario.departamento}</td>
        <td>
          ${
            infoUsuario.activo
              ? '<span class="badge bg-success">Activo</span>'
              : '<span class="badge bg-danger">Inactivo</span>'
          }
        </td>
        <td>
          <a title="Ver detalles del usuario" href="#" onclick="verDetallesEmpleado(${infoUsuario.id})" class="btn btn-success btn-sm">
            <i class="bi bi-binoculars"></i>
          </a>
          <a title="Editar datos del usuario" href="#" onclick="editarEmpleado(${infoUsuario.id})" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil-square"></i>
          </a>
          <a title="Eliminar datos del usuario" href="#" onclick="eliminarEmpleado(${infoUsuario.id})" class="btn btn-danger btn-sm">
            <i class="bi bi-trash"></i>
          </a>
        </td>
      `;

      // Insertar el nuevo usuario al final de la tabla de forma dinámica
      tableBody.appendChild(tr);
    }
  } catch (error) {
    console.error("Error al obtener la información del usuario", error);
  }
};
