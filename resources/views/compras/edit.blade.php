@extends('layouts.app')

@section('titulo', 'Editar Orden de Compra')

@section('contenido')

    <h1 class="mb-3">Editar Orden de Compra</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('compras.update', trim($compra->id_compra)) }}"
          method="POST"
          id="formEditarOC">
        @csrf
        @method('PUT')

        {{-- CABECERA --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h6 class="fw-bold mb-3 text-uppercase"
                    style="color:#660404;border-bottom:2px solid #660404;padding-bottom:6px;">
                    DATOS DE LA ORDEN
                </h6>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">ID Orden</label>
                        <input class="form-control"
                               value="{{ trim($compra->id_compra) }}"
                               disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fecha/Hora</label>
                        <input class="form-control"
                               value="{{ $compra->oc_fecha_hora }}"
                               disabled>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Estado</label>
                        <input class="form-control"
                               value="{{ trim($compra->estado_oc) }}"
                               disabled>
                    </div>
                </div>

                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label">
                            Proveedor <span class="text-danger">*</span>
                        </label>

                        <select name="id_proveedor"
                                id="selectProveedor"
                                class="form-select @error('id_proveedor') is-invalid @enderror">
                            <option value="">Seleccione un proveedor</option>
                            @foreach($proveedores as $prv)
                                @php
                                    $idPrv = trim($prv->id_proveedor);
                                @endphp
                                <option value="{{ $idPrv }}"
                                    {{ old('id_proveedor', trim($compra->id_proveedor)) === $idPrv ? 'selected' : '' }}>
                                    {{ trim($prv->prv_nombre) }}
                                </option>
                            @endforeach
                        </select>

                        @error('id_proveedor')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- DETALLE --}}
        <h4 class="mb-2" style="color:#660404;">
            Detalle de Productos
        </h4>

        <div class="table-responsive mb-3">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="width:42%">Producto</th>
                    <th class="text-end">Valor</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-center">Acción</th>
                </tr>
                </thead>

                <tbody id="contenedor-productos">
                @php
                    $items = old('productos') ?? $detalle->map(fn($d) => [
                        'id_producto' => trim($d->id_producto),
                        'cantidad'    => (int) $d->pxo_cantidad,
                    ])->toArray();
                @endphp

                @foreach($items as $i => $item)
                    <tr class="producto-item">
                        <td>
                            <select name="productos[{{ $i }}][id_producto]"
                                    class="form-select form-select-sm select-producto"
                                    onchange="actualizarPrecio(this)">
                                <option value="">Seleccione un producto</option>
                                @foreach($productos as $p)
                                    <option value="{{ trim($p->id_producto) }}"
                                            data-precio="{{ $p->pro_valor_compra ?? 0 }}"
                                        {{ trim($item['id_producto']) === trim($p->id_producto) ? 'selected' : '' }}>
                                        {{ trim($p->pro_nombre) }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="text-end"><span class="precio">0.00</span></td>

                        <td>
                            <input type="number"
                                   name="productos[{{ $i }}][cantidad]"
                                   class="form-control form-control-sm text-center cantidad"
                                   min="1"
                                   value="{{ $item['cantidad'] }}"
                                   onkeydown="return soloFlechasCantidad(event);"
                                   oninput="actualizarSubtotal(this)">
                        </td>

                        <td class="text-end">
                            <strong class="subtotal">0.00</strong>
                        </td>

                        <td class="text-center">
                            <button type="button"
                                    class="btn btn-danger btn-sm"
                                    onclick="eliminarProducto(this)">
                                Quitar
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <button type="button"
                class="btn btn-concho btn-sm mb-3"
                onclick="agregarProducto()">
            + Agregar producto
        </button>

        {{-- RESUMEN --}}
        <div class="row">
            <div class="col-md-5">
                <table class="table table-bordered">
                    <tr>
                        <th>Subtotal</th>
                        <td class="text-end" id="subtotal-general">0.00</td>
                    </tr>
                    <tr>
                        <th>IVA ({{ (int)($compra->oc_iva ?? 15) }}%)</th>
                        <td class="text-end" id="iva-general">0.00</td>
                    </tr>
                    <tr>
                        <th>TOTAL</th>
                        <td class="text-end">
                            <strong id="total-general">0.00</strong>
                        </td>
                    </tr>
                </table>

                <input type="hidden" name="accion" id="accionGuardar" value="guardar">

                <div class="d-flex gap-2">
                    <a href="{{ route('compras.index') }}"
                       class="btn btn-secondary">
                        Cancelar
                    </a>

                    <button type="submit"
                            class="btn btn-primary"
                            onclick="document.getElementById('accionGuardar').value='guardar'">
                        Guardar
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            onclick="document.getElementById('accionGuardar').value='aprobar'">
                        Guardar y aprobar
                    </button>
                </div>
            </div>
        </div>
    </form>

@endsection
