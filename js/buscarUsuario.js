/**
 * Filtro de búsqueda en tiempo real para la tabla de usuarios
 */
document.addEventListener("DOMContentLoaded", function () {
  const inputBuscar = document.getElementById("inputBuscar");

  // Si el input de búsqueda no existe en esta página, detenemos el script
  if (!inputBuscar) return;

  inputBuscar.addEventListener("input", function () {
    const textoBusqueda = this.value.toLowerCase().trim();

    // Seleccionamos todas las filas del cuerpo de la tabla
    const filasTabla = document.querySelectorAll("table tbody tr");

    filasTabla.forEach((fila) => {
      // Obtenemos todo el texto visible de la fila (Nombre, Rol, Cargo, Departamento, etc.)
      const contenidoFila = fila.textContent.toLowerCase();

      // Si el texto coincide con lo que escribió el usuario, mostramos la fila, si no, la ocultamos
      if (contenidoFila.includes(textoBusqueda)) {
        fila.style.display = ""; // Restablece el display original (Muestra)
      } else {
        fila.style.display = "none"; // Oculta la fila de la vista
      }
    });
  });
});
