/**
 * Modal para confirmar la eliminación de un usuario
 */
async function cargarModalConfirmacion() {
  try {
    // 1. LIMPIEZA: Si quedó alguna modal de confirmación vieja dando vueltas, la eliminamos del mapa
    const existingDeleteModal = document.getElementById("confirmModal");
    if (existingDeleteModal) {
      const modalInstance = bootstrap.Modal.getInstance(existingDeleteModal);
      if (modalInstance) modalInstance.hide();
      existingDeleteModal.closest("div").remove(); // Remueve el contenedor flotante completo
    }

    // Limpiamos la de edición por si acaso
    const existingEditModal = document.getElementById("editarUsuarioModal");
    if (existingEditModal) {
      const modalInstance = bootstrap.Modal.getInstance(existingEditModal);
      if (modalInstance) modalInstance.hide();
      existingEditModal.remove();
    }

    // 2. Solicitamos el cascarón de la modal de confirmación
    const response = await fetch("modales/modalDelete.php");
    if (!response.ok) {
      throw new Error("Error al cargar la modal de confirmación");
    }

    const modalHTML = await response.text();

    // 3. Creamos e inyectamos el contenedor flotante
    const modalContainer = document.createElement("div");
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer);

    const modalElement = modalContainer.querySelector(".modal");
    const myModal = new bootstrap.Modal(modalElement);
    myModal.show();

    // 4. TRUCO DE ORO: Cuando la modal termine de ocultarse, se autodestruye del HTML
    modalElement.addEventListener("hidden.bs.modal", function () {
      modalContainer.remove();
    });
  } catch (error) {
    console.error(error);
  }
}

/**
 * Función principal para eliminar un usuario en PostgreSQL
 */
async function eliminarEmpleado(idUsuario) {
  try {
    // Cargamos la modal limpia y única
    await cargarModalConfirmacion();

    const btnConfirmar = document.getElementById("confirmDeleteBtn");
    if (btnConfirmar) {
      btnConfirmar.setAttribute("data-id", idUsuario);
    }

    // Escuchamos el clic definitivo de confirmación
    document
      .getElementById("confirmDeleteBtn")
      .addEventListener("click", async function () {
        var idParaBorrar = this.getAttribute("data-id");

        // Quitamos el foco del botón para evitar advertencias de accesibilidad (ARIA)
        if (document.activeElement) {
          document.activeElement.blur();
        }

        try {
          const response = await axios.post("acciones/delete.php", {
            id: idParaBorrar,
          });

          if (response.status === 200 && response.data.success) {
            // Tu validación blindada para remover la fila en vivo
            const filaTabla = document.querySelector(
              `#usuario_${idParaBorrar}`,
            );
            if (filaTabla) {
              filaTabla.remove();
            }

            // Disparamos la alerta animada
            showToast.warning("¡Usuario eliminado con éxito!", {
              duration: 4000,
              progress: true,
              position: "bottom-right",
              transition: "bounceInDown",
              icon: "",
              sound: true,
            });
          } else {
            showToast.error(
              response.data.message ||
                `Error al eliminar el usuario con ID ${idParaBorrar}`,
              {
                duration: 4000,
                position: "bottom-right"
              }
            );
          }
        } catch (error) {
          console.error(error);
          showToast.error("Hubo un problema al eliminar al usuario en la base de datos", {
            duration: 4000,
            position: "bottom-right"
          });
        } finally {
          // Cerramos la modal de forma controlada
          const confirmModalEl = document.getElementById("confirmModal");
          if (confirmModalEl) {
            const confirmModal = bootstrap.Modal.getInstance(confirmModalEl);
            if (confirmModal) confirmModal.hide();
          }
        }
      });
  } catch (error) {
    console.error(error);
    showToast.error("Hubo un problema al procesar la modal de confirmación", {
      duration: 4000,
      position: "bottom-right"
    });
  }
}
