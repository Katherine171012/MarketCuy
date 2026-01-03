<div class="card border-0 shadow-sm rounded-3 mb-4">

    <div class="card-header py-2" style="background-color: #660404; border-bottom: 1px solid #660404;">
        <span class="fw-bold text-white small">Editando Cliente: {{ $clienteEdit->id_cliente }}</span>
    </div>

    <div class="card-body p-4">
        <form method="POST" action="{{ route('clientes.update', $clienteEdit) }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">ID Cliente</label>
                    <input type="text" value="{{ $clienteEdit->id_cliente }}" class="form-control bg-light border-0" readonly>
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Identificación</label>
                    <input type="text" value="{{ $clienteEdit->cli_ruc_ced }}" class="form-control bg-light border-0" readonly>
                </div>

                <div class="col-md-12">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Nombre *</label>
                    <input type="text" name="cli_nombre" value="{{ old('cli_nombre', $clienteEdit->cli_nombre) }}"
                           class="form-control border-warning border-opacity-50 shadow-sm" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Ciudad *</label>
                    <select name="id_ciudad" class="form-select border-0 bg-light shadow-sm" required>
                        @foreach($ciudades as $ciu)
                            <option value="{{ $ciu->id_ciudad }}" {{ $clienteEdit->id_ciudad == $ciu->id_ciudad ? 'selected' : '' }}>
                                {{ $ciu->ciu_descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Correo Electrónico *</label>
                    <input type="email" name="cli_mail" value="{{ old('cli_mail', $clienteEdit->cli_mail) }}" class="form-control border-0 bg-light shadow-sm" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Celular *</label>
                    <input type="text" name="cli_celular" value="{{ old('cli_celular', $clienteEdit->cli_celular) }}" class="form-control border-0 bg-light shadow-sm" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted mb-1 text-uppercase">Dirección</label>
                    <input type="text" name="cli_direccion" value="{{ old('cli_direccion', $clienteEdit->cli_direccion) }}" class="form-control border-0 bg-light shadow-sm">
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm fw-bold px-4 py-2 shadow-sm" style="background-color: #660404; color: #ffffff; border: none; border-radius: 6px;">
                    Guardar Cambios
                </button>
                <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-secondary fw-bold px-4 py-2" style="border-radius: 6px;">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
