@php
    $id     = isset($compra) ? trim((string) $compra->id_compra) : '';
    $estado = isset($compra) ? trim((string) $compra->estado_oc) : '';
@endphp

<div class="modal fade" id="modalEliminarCompra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="text-muted">
                    ¿Estás seguro de eliminar (anular) la orden de compra
                    <span class="fw-bold text-dark">{{ $id }}</span>?
                </div>

                <div class="small text-muted mt-2">
                    La OC no se elimina físicamente; se marcará como <strong>ANU</strong>.
                    @if($estado !== '')
                        <br>Estado actual: <strong>{{ $estado }}</strong>.
                    @endif
                </div>
            </div>

            <div class="modal-footer">
                <a href="{{ route('compras.index') }}" class="btn btn-secondary fw-bold px-4">
                    Cancelar
                </a>

                @if($id !== '')
                    <form method="POST" action="{{ route('compras.destroy', $id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger fw-bold px-4">
                            Sí, eliminar
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalEliminarCompra');
        if (!modalEl) return;
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
</script>
