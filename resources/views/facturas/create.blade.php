@extends('layouts.app')

@section('titulo', 'Generar Factura')

@section('content')

    <h1 class="mb-3">Generar Factura</h1>

    {{-- MENSAJES DE ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('facturas.store') }}" method="POST" id="formCrearFactura">
        @csrf

        {{-- CLIENTE / DESCRIPCIÓN --}}
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">
                    Cliente <span class="text-danger">*</span>
                </label>
                <select name="id_cliente" class="form-select">
                    <option value="">Seleccione un cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id_cliente }}"
                            {{ old('id_cliente') == $cliente->id_cliente ? 'selected' : '' }}>
                            {{ $cliente->cli_nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Descripción</label>
                <input type="text"
                       name="fac_descripcion"
                       class="form-control"
                       maxlength="30"
                       value="{{ old('fac_descripcion') }}">
            </div>
        </div>

        {{-- BOTONES --}}
        <div class="d-flex gap-3">
            <a href="{{ route('facturas.index') }}"
               class="btn btn-concho px-5 text-nowrap">
                Cancelar
            </a>

            <button type="submit"
                    class="btn btn-concho px-5 text-nowrap">
                Guardar
            </button>
        </div>
    </form>

@endsection
