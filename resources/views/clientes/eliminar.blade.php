<div class="modal fade show" style="display: block; background: rgba(0,0,0,0.6);" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document"> {{-- Centrado --}}
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">

            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="color: #333;">Confirmar eliminación</h5>
                <a href="{{ route('clientes.index') }}" class="btn-close" aria-label="Close"></a>
            </div>

            <div class="modal-body px-4">
                <p class="text-secondary" style="font-size: 1.05rem;">
                    ¿Estás seguro de eliminar el cliente <br>
                    <span class="fw-bold text-dark">{{ $cliente->id_cliente }} – {{ $cliente->cli_nombre }}</span>?
                </p>
            </div>

            <div class="modal-footer border-0 pb-4 px-4">
                <a href="{{ route('clientes.cancelarEliminacion') }}" class="btn btn-secondary fw-bold px-4 py-2" style="border-radius: 8px; border: none; background-color: #6c757d;">
                    Cancelar
                </a>

                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger fw-bold px-4 py-2" style="border-radius: 8px; border: none; background-color: #dc3545;">
                        Sí, eliminar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
