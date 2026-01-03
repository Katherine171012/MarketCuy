@extends('layouts.app')

@section('content')
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background-color: #660404;">
        <div class="container">
            <span class="navbar-brand fw-bold font-monospace">MarketCuy</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link disabled text-white-50 small">Clientes</span>
            </div>
        </div>
    </nav>

    <div class="container">
        <h2 class="fw-bold mb-4" style="color: #333;">Ingresar Nuevo Cliente</h2>

        @if(session('codigo_mensaje'))
            @php
                $codigo = session('codigo_mensaje');
                $claseAlerta = str_starts_with($codigo, 'E') ? 'alert-danger' : 'alert-warning';
            @endphp
            <div class="alert {{ $claseAlerta }} border-0 shadow-sm fw-bold mb-4 py-3">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ config("mensajes.$codigo") ?? "Código: $codigo" }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="m-0 fw-bold text-concho text-uppercase small">Datos del Cliente </h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('clientes.store') }}">
                    @csrf

                    <div class="row g-4">

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Cédula / RUC *</label>
                            <input type="text" name="cli_ruc_ced" value="{{ old('cli_ruc_ced') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="10 o 13 dígitos" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Nombre Completo *</label>
                            <input type="text" name="cli_nombre" value="{{ old('cli_nombre') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="Ej: Juan Pérez" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Ciudad *</label>
                            <select name="id_ciudad" class="form-select form-select-lg bg-light border-0 shadow-sm" required>
                                <option value="">-- Seleccione una ciudad --</option>
                                @foreach($ciudades as $ciu)
                                    <option value="{{ $ciu->id_ciudad }}" {{ old('id_ciudad') == $ciu->id_ciudad ? 'selected' : '' }}>
                                        {{ $ciu->ciu_descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-muted small text-uppercase">Correo Electrónico *</label>
                            <input type="email" name="cli_mail" value="{{ old('cli_mail') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="nombre@ejemplo.com" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Teléfono Fijo</label>
                            <input type="text" name="cli_telefono" value="{{ old('cli_telefono') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="Opcional">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Celular *</label>
                            <input type="text" name="cli_celular" value="{{ old('cli_celular') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="09XXXXXXXX" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Dirección</label>
                            <input type="text" name="cli_direccion" value="{{ old('cli_direccion') }}"
                                   class="form-control form-control-lg bg-light border-0 shadow-sm"
                                   placeholder="Calle, Número, Sector">
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-top d-flex gap-2">
                        <button type="submit" class="btn btn-concho fw-bold px-5 py-2 shadow-sm" style="border-radius: 8px;">
                            Guardar Cliente
                        </button>
                        <a href="{{ route('clientes.index') }}" class="btn btn-light border fw-bold px-5 py-2 text-secondary shadow-sm" style="border-radius: 8px;">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <div class="mt-4 mb-5">
            <small class="text-muted">Los campos marcados con (*) son obligatorios para el sistema.</small>
        </div>
    </div>
@endsection
