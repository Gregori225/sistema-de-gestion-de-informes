<div class="modal fade" id="confirmModalDep" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title titulo_modal">Por favor Confirmar tu Acción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar este departamento del sistema?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <!-- JS controlará el cierre después de que Axios termine con la petición -->
                <button type="button" class="btn btn-danger" id="confirmDeleteDepBtn">Eliminar departamento</button>
            </div>
        </div>
    </div>
</div>