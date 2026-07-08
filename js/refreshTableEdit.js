// Define la función globalmente adjuntándola al objeto window
window.actualizarUsuarioEdit = async function (idUsuario) {
  try {
    const response = await axios.get(
      `acciones/getEmpleado.php?id=${idUsuario}`
    );
    if (response.status === 200) {
      const infoUsuario = response.data; // Obtener los datos del usuario desde la respuesta

      let tr = document.querySelector(`#usuario_${idUsuario}`);
      let tablaHTML = "";
      tablaHTML += `
          <tr id="usuario_${infoUsuario.id}">
            <th class="dt-type-numeric sorting_1" scope="row">${
              infoUsuario.id
            }</th>
            <td>${infoUsuario.nombre}</td>
            <td>${infoUsuario.usuario}</td>
            <td>${infoUsuario.cargo}</td>
            <td>${infoUsuario.rol}</td>
            <td>${infoUsuario.departamento || 'N/A'}</td>
            <td>${
              infoUsuario.activo 
                ? '<span class="badge bg-success">Activo</span>' 
                : '<span class="badge bg-danger">Inactivo</span>'
            }</td>
            <td>
              <a title="Ver detalles del usuario" href="#" onclick="verDetallesEmpleado(${
                infoUsuario.id
              })" class="btn btn-success"><i class="bi bi-binoculars"></i></a>
              <a title="Editar datos del usuario" href="#" onclick="editarEmpleado(${
                infoUsuario.id
              })" class="btn btn-warning"><i class="bi bi-pencil-square"></i></a>
              <a title="Eliminar datos del usuario" href="#" onclick="eliminarEmpleado(${
                infoUsuario.id
              })" class="btn btn-danger"><i class="bi bi-trash"></i></a>
            </td>
          </tr>
        `;

      // Actualizar el contenido HTML de la tabla
      tr.innerHTML = tablaHTML;
    }
  } catch (error) {
    console.error("Error al obtener la información del usuario", error);
    showToast.error("Error al actualizar la fila del usuario", {
      duration: 4000,
      position: "bottom-right"
    });
  }
};
