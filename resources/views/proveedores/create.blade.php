@extends('layouts.app')

@section('titulo', 'Nuevo Proveedor')

@section('contenido')

    <h1 class="mb-3">Ingresar Nuevo Proveedor</h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3">DATOS DEL PROVEEDOR</h6>

            <form method="POST" action="{{ route('proveedores.store') }}">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">ID Proveedor</label>
                        <input type="text" class="form-control" value="{{ $idProveedor }}" disabled>
                        <small class="text-muted">Se genera automáticamente.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <input type="text" class="form-control" value="ACT" disabled>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nombre *</label>
                        <input type="text" name="prv_nombre"
                               class="form-control @error('prv_nombre') is-invalid @enderror"
                               value="{{ old('prv_nombre') }}" placeholder="Ej: Proveedor Santo Domingo">
                        @error('prv_nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">RUC / Cédula *</label>
                        <input type="text" name="prv_ruc_ced"
                               class="form-control @error('prv_ruc_ced') is-invalid @enderror"
                               value="{{ old('prv_ruc_ced') }}" placeholder="10 o 13 dígitos">
                        @error('prv_ruc_ced')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Ciudad *</label>
                        <select name="id_ciudad" class="form-select @error('id_ciudad') is-invalid @enderror">
                            <option value="">-- Seleccione una ciudad --</option>
                            @foreach($ciudades as $c)
                                <option value="{{ trim($c->id_ciudad) }}" {{ old('id_ciudad') == trim($c->id_ciudad) ? 'selected' : '' }}>
                                    {{ trim($c->ciu_descripcion) }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_ciudad')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Correo electrónico *</label>
                        <input type="email" name="prv_mail"
                               class="form-control @error('prv_mail') is-invalid @enderror"
                               value="{{ old('prv_mail') }}" placeholder="correo@ejemplo.com">
                        @error('prv_mail')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono fijo *</label>
                        <input type="text" name="prv_telefono"
                               class="form-control @error('prv_telefono') is-invalid @enderror"
                               value="{{ old('prv_telefono') }}" placeholder="10 dígitos">
                        @error('prv_telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Celular</label>
                        <input type="text" name="prv_celular"
                               class="form-control @error('prv_celular') is-invalid @enderror"
                               value="{{ old('prv_celular') }}" placeholder="Opcional (10 dígitos)">
                        @error('prv_celular')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Dirección</label>
                        <input type="text" name="prv_direccion"
                               class="form-control @error('prv_direccion') is-invalid @enderror"
                               value="{{ old('prv_direccion') }}" placeholder="Calle, Número, Sector">
                        @error('prv_direccion')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Guardar Proveedor</button>
                    <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>

                <div class="mt-3">
                    <small class="text-muted">Los campos marcados con (*) son obligatorios para el sistema.</small>
                </div>
            </form>
        </div>
    </div>

@endsection
