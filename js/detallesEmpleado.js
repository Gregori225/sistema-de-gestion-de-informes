/**
 * Función para mostrar la modal de detalles del usuario
 */
async function verDetallesEmpleado(idUsuario) {
  try {
    // Ocultar la modal si está abierta
    const existingModal = document.getElementById("detalleEmpleadoModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove(); // Eliminar la modal existente para evitar duplicados
    }

    // Buscar la Modal de Detalles
    const response = await fetch("modales/modalDetalles.php");
    if (!response.ok) {
      throw new Error("Error al cargar la modal de detalles del usuario");
    }
    const modalHTML = await response.text();

    // Crear un elemento div para almacenar el contenido de la modal
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = modalHTML;

    // Agregar la modal al documento actual
    document.body.appendChild(modalContainer);

    // Mostrar la modal
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#detalleEmpleadoModal"),
    );
    myModal.show();

    // Ejecutamos la carga con el ID del usuario
    await cargarDetalleEmpleado(idUsuario);
  } catch (error) {
    console.error(error);
    showToast.error("Error al cargar la modal de detalles", {
      duration: 4000,
      position: "bottom-right"
    });
  }
}

/**
 * Función para cargar y mostrar los detalles del usuario en la modal desde PostgreSQL
 */
async function cargarDetalleEmpleado(idUsuario) {
  try {
    // Apuntamos al nuevo endpoint que creamos con el INNER JOIN de departamentos
    const response = await axios.get(
      `acciones/detallesUsuarioCompleto.php?id=${idUsuario}`,
    );

    if (response.status === 200) {
      console.log(response.data);

      // Desestructuramos los campos reales de tu base de datos en Postgres
      const { nombre, usuario, rol, cargo, departamento, activo } =
        response.data;

      // Buscamos la lista ul dentro del contenedor de la modal
      const ulDetalleEmpleado = document.querySelector(
        "#detalleEmpleadoContenido ul",
      );

      if (ulDetalleEmpleado) {
        // Reemplazamos las li viejas por las nuevas columnas
        // Añadí clases de Bootstrap (d-flex) para que la información se vea alineada y limpia
        ulDetalleEmpleado.innerHTML = ` 
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Nombre Completo:</b></span>
            <span class="text-muted">${nombre ? nombre : "No disponible"}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Usuario (Login):</b></span>
            <span class="badge bg-secondary">${usuario ? usuario : "No disponible"}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Rol en Sistema:</b></span>
            <span class="text-muted">${rol === "admin" ? "Administrador" : "Usuario"}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Cargo:</b></span>
            <span class="text-muted">${cargo ? cargo : "No disponible"}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Departamento:</b></span>
            <span class="text-dark fw-semibold">${departamento ? departamento : "No disponible"}</span>
          </li>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><b>Estado Cuenta:</b></span>
            <span>${
              activo
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>'
            }</span>
          </li>
        `;
      }
    } else {
      showToast.error(`Error al cargar los detalles del usuario con ID ${idUsuario}`, {
        duration: 4000,
        position: "bottom-right"
      });
    }
  } catch (error) {
    console.error(error);
    showToast.error("Hubo un problema al cargar los detalles del usuario", {
      duration: 4000,
      position: "bottom-right"
    });
  }
}
