/**
 * Modal para agregar un nuevo usuario
 */
async function modalRegistrarEmpleado() {
  try {
    // Ocultar la modal si está abierta
    const existingModal = document.getElementById("detalleEmpleadoModal");
    if (existingModal) {
      const modal = bootstrap.Modal.getInstance(existingModal);
      if (modal) {
        modal.hide();
      }
      existingModal.remove();
    }

    // Eliminar modal anterior de agregar usuario si existe
    const existingAddModal = document.getElementById("agregarEmpleadoModal");
    if (existingAddModal) {
      const modal = bootstrap.Modal.getInstance(existingAddModal);
      if (modal) {
        modal.hide();
      }
      existingAddModal.parentElement.remove();
    }

    // Buscamos el archivo que creará el formulario flotante
    const response = await fetch("modales/modalAdd.php");

    if (!response.ok) {
      throw new Error("Error al cargar la modal");
    }

    const data = await response.text();

    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = data;

    document.body.appendChild(modalContainer);

    const myModal = new bootstrap.Modal(
      modalContainer.querySelector("#agregarEmpleadoModal"),
    );
    myModal.show();
  } catch (error) {
    console.error(error);
  }
}

/**
 * Función para enviar el formulario al backend
 */
async function registrarEmpleado(event) {
  try {
    event.preventDefault();

    const formulario = document.querySelector("#formularioEmpleado");
    const formData = new FormData(formulario);

    // Envía los datos a acciones.php (que ya modificamos para Postgres)
    const response = await axios.post("acciones/acciones.php", formData);

    if (response.status === 200) {
      formulario.reset();

      window.insertarUsuarioTabla();

      setTimeout(() => {
        const modalEl = document.getElementById("agregarEmpleadoModal");
        modalEl.style.opacity = "";
        bootstrap.Modal.getInstance(modalEl).hide();

        // TEXTO MODIFICADO: Ahora dice Usuario
        showToast.success("¡Usuario registrado con éxito!", {
          duration: 4000,
          progress: true,
          position: "top-left",
          transition: "swingInverted",
          icon: "",
          sound: true,
        });
      }, 600);
    } else {
      console.error("Error al registrar el usuario");
    }
  } catch (error) {
    console.error("Error al enviar el formulario", error);
  }
}
