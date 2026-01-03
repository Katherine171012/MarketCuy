{{-- Vista SHOW para confirmar eliminación --}}

<div class="card border-danger">
    <div class="card-header bg-danger text-white fw-semibold">
        Confirmar Eliminación
    </div>

    <div class="card-body">
        <p class="mb-3">
            ¿Está seguro que desea eliminar el siguiente producto?
        </p>

        <ul class="list-group mb-3">
            <li class="list-group-item">
                <strong>ID:</strong> {{ $productoEliminar->id_producto }}
            </li>
            <li class="list-group-item">
                <strong>Descripción:</strong> {{ $productoEliminar->pro_descripcion }}
            </li>
            <li class="list-group-item">
                <strong>Precio:</strong> {{ $productoEliminar->pro_precio_venta }}
            </li>
            <li class="list-group-item">
                <strong>Stock:</strong> {{ $productoEliminar->pro_saldo_final }}
            </li>
        </ul>

        <div class="d-flex gap-2">
            <form method="POST"
                  action="{{ route('productos.destroy', $productoEliminar->id_producto) }}">
                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-danger">
                    Sí, eliminar
                </button>
            </form>

            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                Cancelar
            </a>
        </div>
    </div>
</div>
