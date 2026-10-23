// assets/js/departamentos.js

/**
 * Inserta un nuevo departamento asíncronamente
 */
function guardarDepartamento() {
  const inputNombre = document.getElementById("nombreDep");
  const nombre = inputNombre.value.trim();

  if (!nombre) {
    showToast.error("El campo nombre no puede estar vacío.", {
      duration: 4000,
      position: "bottom-right",
    });
    return;
  }

  axios
    .post("acciones/addDepartamento.php", { nombre: nombre })
    .then((response) => {
      if (response.data.success) {
        const { id, nombre } = response.data;
        const tbody = document.querySelector("#tablaDepartamentos tbody");

        // Nueva Fila Semántica
        const nuevaFila = `
            <tr id="dep-${id}">
                <td><strong>${id}</strong></td>
                <td>${nombre}</td>
                <td class="text-center">
                    <button class="btn btn-outline-danger btn-sm" onclick="eliminarDepartamento(${id})" title="Eliminar Departamento">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        // Agrega el registro al inicio de la tabla
        tbody.insertAdjacentHTML("afterbegin", nuevaFila);

        // Resetear el formulario
        document.getElementById("formDepartamento").reset();

        // Cerrar modal de manera limpia usando la API nativa de Bootstrap 5
        const modalElement = document.getElementById("addDepartamentoModal");
        const modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
        modalInstance.hide();

        // Liberar el foco del cursor
        if (document.activeElement) document.activeElement.blur();

        // Alerta de éxito animada
        showToast.warning("¡Departamento creado con éxito!", {
          duration: 4000,
          progress: true,
          position: "bottom-right",
          transition: "bounceInDown",
          sound: true,
        });
      }
    })
    .catch((error) => {
      console.error(error);
      const msg =
        error.response?.data?.error || "Error al procesar el registro.";
      showToast.error(msg, {
        duration: 4000,
        position: "bottom-right",
      });
    });
}

/**
 * Modal para confirmar la eliminación de un departamento (Carga dinámica)
 */
async function cargarModalConfirmacionDep() {
  try {
    // LIMPIEZA: Barremos modales viejos de departamentos del DOM si quedaron flotando
    const existingDeleteModal = document.getElementById("confirmModalDep");
    if (existingDeleteModal) {
      const modalInstance = bootstrap.Modal.getInstance(existingDeleteModal);
      if (modalInstance) modalInstance.hide();
      existingDeleteModal.closest("div").remove();
    }

    // Solicitamos el cascarón de la modal a nuestro servidor
    const response = await fetch("modales/modalDeleteDepartamento.php");
    if (!response.ok) {
      throw new Error(
        "Error al cargar la modal de confirmación de departamento",
      );
    }

    const modalHTML = await response.text();

    // Creamos e inyectamos el contenedor flotante al DOM
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);

    const modalElement = modalContainer.querySelector(".modal");
    const myModal = new bootstrap.Modal(modalElement);
    myModal.show();

    // TRUCO DE ORO: Autodestrucción del HTML al cerrarse
    modalElement.addEventListener("hidden.bs.modal", function () {
      modalContainer.remove();
    });
  } catch (error) {
    console.error(error);
  }
}

/**
 * Función principal para eliminar un departamento en PostgreSQL
 */
async function eliminarDepartamento(idDepartamento) {
  try {
    await cargarModalConfirmacionDep();

    const btnConfirmar = document.getElementById("confirmDeleteDepBtn");
    if (btnConfirmar) {
      btnConfirmar.setAttribute("data-id", idDepartamento);
    }

    // Escuchamos el clic definitivo del botón de confirmación
    document
      .getElementById("confirmDeleteDepBtn")
      .addEventListener("click", async function () {
        const idParaBorrar = this.getAttribute("data-id");

        if (document.activeElement) {
          document.activeElement.blur();
        }

        try {
          const response = await axios.delete(
            `acciones/deleteDepartamento.php?id=${idParaBorrar}`,
          );

          if (response.status === 200 && response.data.success) {
            const filaTabla = document.querySelector(`#dep-${idParaBorrar}`);
            if (filaTabla) {
              filaTabla.style.transition =
                "all 0.3s cubic-bezier(0.4, 0, 0.2, 1)";
              filaTabla.style.opacity = "0";
              filaTabla.style.transform = "translateX(20px)";
              setTimeout(() => filaTabla.remove(), 300);
            }

            showToast.warning("¡Departamento eliminado con éxito!", {
              duration: 4000,
              progress: true,
              position: "bottom-right",
              transition: "bounceInDown",
              sound: true,
            });
          }
        } catch (error) {
          console.error(error);
          const msgError =
            error.response?.data?.error ||
            "Hubo un problema al eliminar el departamento.";

          showToast.error(msgError, {
            duration: 5000,
            progress: true,
            position: "bottom-right",
            transition: "bounceInDown",
            sound: true,
          });
        } finally {
          const confirmModalEl = document.getElementById("confirmModalDep");
          if (confirmModalEl) {
            const confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
            if (confirmModal) confirmModal.hide();
          }
        }
      });
  } catch (error) {
    console.error(error);
  }
}
