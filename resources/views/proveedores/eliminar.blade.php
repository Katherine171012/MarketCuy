@php
    $id = isset($proveedor) ? trim((string) $proveedor->id_proveedor) : '';
    $nombre = isset($proveedor) ? trim((string) $proveedor->prv_nombre) : '';
@endphp

<div class="modal fade" id="modalEliminarProveedor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="text-muted">
                    ¿Estás seguro de eliminar (inactivar) el proveedor
                    <span class="fw-bold text-dark">{{ $id }} – {{ $nombre }}</span>?
                </div>
            </div>

            <div class="modal-footer">
                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary fw-bold px-4">
                    Cancelar
                </a>

                @if($id !== '')
                    <form method="POST" action="{{ route('proveedores.destroy', $id) }}">
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
        var modalEl = document.getElementById('modalEliminarProveedor');
        if (!modalEl) return;
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
</script>
