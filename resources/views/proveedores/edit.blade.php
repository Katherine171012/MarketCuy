@php
    $soloLectura = isset($modo) && $modo === 'view';
    $titulo = $soloLectura ? 'Visualizando Proveedor: ' : 'Editando Proveedor: ';
@endphp

<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-header text-white fw-bold" style="background-color:#660404;">
        {{ $titulo }} {{ trim($proveedor->id_proveedor) }}
    </div>

    <div class="card-body bg-white">
        <form method="POST"
              action="{{ $soloLectura ? '#' : route('proveedores.update', trim($proveedor->id_proveedor)) }}">
            @csrf
            @if(!$soloLectura)
                @method('PUT')
            @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">ID PROVEEDOR</label>
                    <input type="text" class="form-control"
                           value="{{ $proveedor->id_proveedor }}"
                           readonly style="background:#f1f1f1;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">ESTADO</label>
                    <input type="text" class="form-control"
                           value="{{ trim($proveedor->estado_prv) }}"
                           readonly style="background:#f1f1f1;">
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">FECHA INGRESO</label>
                    <input type="text" class="form-control"
                           value="{{ optional($proveedor->fecha_ingreso)->format('Y-m-d') }}"
                           readonly style="background:#f1f1f1;">
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted fw-bold">NOMBRE *</label>
                    <input name="prv_nombre" type="text" class="form-control"
                           value="{{ old('prv_nombre', $proveedor->prv_nombre) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_nombre') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small text-muted fw-bold">RUC / CÉDULA *</label>
                    <input name="prv_ruc_ced" type="text" class="form-control"
                           value="{{ old('prv_ruc_ced', $proveedor->prv_ruc_ced) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_ruc_ced') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label small text-muted fw-bold">CORREO ELECTRÓNICO *</label>
                    <input name="prv_mail" type="email" class="form-control"
                           value="{{ old('prv_mail', $proveedor->prv_mail) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_mail') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">CIUDAD *</label>
                    <select name="id_ciudad" class="form-select"
                            {{ $soloLectura ? 'disabled' : '' }}
                            style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                        <option value="">-- Seleccione una ciudad --</option>
                        @foreach($ciudades as $c)
                            @php
                                $idC = trim($c->id_ciudad);
                                $sel = old('id_ciudad', trim($proveedor->id_ciudad)) == $idC;
                            @endphp
                            <option value="{{ $idC }}" {{ $sel ? 'selected' : '' }}>
                                {{ trim($c->ciu_descripcion) }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_ciudad') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">TELÉFONO *</label>
                    <input name="prv_telefono" type="text" class="form-control"
                           value="{{ old('prv_telefono', $proveedor->prv_telefono) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_telefono') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label small text-muted fw-bold">CELULAR</label>
                    <input name="prv_celular" type="text" class="form-control"
                           value="{{ old('prv_celular', $proveedor->prv_celular) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_celular') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label small text-muted fw-bold">DIRECCIÓN</label>
                    <input name="prv_direccion" type="text" class="form-control"
                           value="{{ old('prv_direccion', $proveedor->prv_direccion) }}"
                           {{ $soloLectura ? 'disabled' : '' }}
                           style="{{ $soloLectura ? 'background:#f1f1f1;' : '' }}">
                    @error('prv_direccion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                @if(!$soloLectura)
                    <button type="submit" class="btn btn-primary fw-bold px-4">
                        Guardar Cambios
                    </button>
                @endif

                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary fw-bold px-4">
                    {{ $soloLectura ? 'Cerrar' : 'Cancelar' }}
                </a>
            </div>
        </form>
    </div>
</div>
