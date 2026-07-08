/**
 * Función para mostrar la modal de editar el usuario
 */
async function editarEmpleado(idUsuario) {
  try {
    // Ocultar y remover la modal si quedó alguna abierta anteriormente
    const existingModal = document.getElementById("editarUsuarioModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove();
    }

    // Buscamos la estructura limpia de la modal de edición
    const response = await fetch("modales/modalEditar.php");
    if (!response.ok) {
      throw new Error("Error al cargar la modal de editar el usuario");
    }
    const modalHTML = await response.text();

    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);

    // Mostramos la modal de Bootstrap usando el nuevo ID adaptado
    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#editarUsuarioModal"),
    );
    myModal.show();

    // Ejecutamos la carga de datos reales de PostgreSQL pasándole el ID
    await cargarDatosUsuarioEditar(idUsuario);
  } catch (error) {
    console.error(error);
    showToast.error("Error al cargar la modal de edición", {
      duration: 4000,
      position: "bottom-right",
    });
  }
}

/**
 * Función que busca la información del usuario en Postgres y la inyecta en los inputs
 */
async function cargarDatosUsuarioEditar(idUsuario) {
  try {
    // Apuntamos al nuevo endpoint de detalles de usuario
    const response = await axios.get(
      `acciones/detallesUsuario.php?id=${idUsuario}`,
    );

    if (response.status === 200) {
      const { id, nombre, usuario, rol, cargo, id_departamento, activo } =
        response.data;

      // Inyectamos los valores en los campos correspondientes
      // Usamos sufijos "_edit" en los IDs para que no colisionen con la modal de Agregar
      document.querySelector("#id_usuario").value = id;
      document.querySelector("#nombre_edit").value = nombre;
      document.querySelector("#usuario_edit").value = usuario;
      document.querySelector("#cargo_edit").value = cargo;
      document.querySelector("#rol_edit").value = rol;
      document.querySelector("#id_departamento_edit").value = id_departamento;

      // Controlamos el select del estado Activo/Inactivo
      const selectActivo = document.querySelector("#activo_edit");
      if (selectActivo) {
        // Convertimos el booleano de Postgres a texto ("true"/"false") para el select
        selectActivo.value = activo ? "true" : "false";
      }
    } else {
      console.log("Error al cargar el usuario a editar");
      showToast.error("Error al cargar los datos del usuario", {
        duration: 4000,
        position: "bottom-right",
      });
    }
  } catch (error) {
    console.error(error);
    showToast.error("Hubo un problema al cargar los detalles del usuario", {
      duration: 4000,
      position: "bottom-right",
    });
  }
}

/**
 * Función para procesar el envío del formulario editado
 */
async function actualizarUsuario(event) {
  try {
    event.preventDefault();

    const formulario = document.querySelector("#formularioUsuarioEdit");
    const formData = new FormData(formulario);
    const idUsuario = formData.get("id"); // Obtiene el ID del input hidden

    // Enviamos los cambios al backend mediante POST
    const response = await axios.post(
      "acciones/actualizarUsuario.php",
      formData,
    );

    if (response.status === 200) {
      console.log("Usuario actualizado exitosamente");

      // Llamamos a la función encargada de refrescar la fila modificada en la tabla
      if (window.actualizarUsuarioEdit) {
        window.actualizarUsuarioEdit(idUsuario);
      } else {
        // Alternativa de seguridad si el script de refresco aún no está listo
        location.reload();
      }

      // Desplegamos tu alerta flotante original modificando el texto
      showToast.info("¡Usuario actualizado con éxito!", {
        duration: 4000,
        progress: true,
        position: "top-left",
        transition: "swingInverted",
        icon: "",
      });

      setTimeout(() => {
        const modalEl = document.getElementById("editarUsuarioModal");
        if (modalEl) {
          modalEl.style.opacity = "";
          bootstrap.Modal.getInstance(modalEl).hide();
        }
      }, 600);
    } else {
      console.error("Error al actualizar el usuario");
      showToast.error("Error al actualizar el usuario", {
        duration: 4000,
        position: "bottom-right",
      });
    }
  } catch (error) {
    console.error("Error al enviar el formulario", error);
    showToast.error("Error al enviar el formulario", {
      duration: 4000,
      position: "bottom-right",
    });
  }
}
