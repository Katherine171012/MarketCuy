@extends('layouts.app')

@section('titulo', 'Buscar Proveedor')

@section('contenido')

    <h1>Consulta por Parámetro</h1>

    <div class="card">
        <div class="card-header">
            Búsqueda de Proveedores
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('proveedores.buscar') }}">

                <label>Buscar por</label>
                <select name="campo">
                    <option value="prv_nombre">Nombre</option>
                    <option value="prv_ruc_ced">RUC / Cédula</option>
                    <option value="prv_mail">Correo</option>
                </select>

                <input type="text" name="valor" placeholder="Ingrese valor">

                <button class="btn btn-primary">Buscar</button>
            </form>
        </div>
    </div>

    @if(isset($proveedores))
        <div class="card">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Ciudad</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                @foreach($proveedores as $p)
                    <tr>
                        <td>{{ $p->id_proveedor }}</td>
                        <td>{{ $p->prv_nombre }}</td>
                        <td>{{ optional($p->ciudad)->ciu_descripcion }}</td>
                        <td>{{ $p->estado_prv }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

@endsection

