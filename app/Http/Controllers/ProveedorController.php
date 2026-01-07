<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Ciudad;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);

        $parametro = $request->get('parametro');
        $valor = trim((string) $request->get('valor', ''));
        $orden = $request->get('orden');

        $query = Proveedor::construirQueryIndex($parametro, $valor, $orden);

        $proveedores = $query->paginate($perPage)->appends($request->query());

        $idEdit = trim((string) $request->get('edit', ''));
        $idView = trim((string) $request->get('view', ''));
        $idDel  = trim((string) $request->get('delete', ''));

        $proveedorEdit = $idEdit !== '' ? Proveedor::with('ciudad')->find($idEdit) : null;
        $proveedorView = $idView !== '' ? Proveedor::with('ciudad')->find($idView) : null;
        $proveedorDelete = $idDel !== '' ? Proveedor::find($idDel) : null;

        $ciudades = Ciudad::query()->orderBy('ciu_descripcion')->get();

        return view('proveedores.index', compact(
            'proveedores',
            'proveedorEdit',
            'proveedorView',
            'proveedorDelete',
            'ciudades'
        ));
    }
    public function create()
    {
        $ciudades = Ciudad::query()->orderBy('ciu_descripcion')->get();
        $idProveedor = Proveedor::generarNuevoId();

        return view('proveedores.create', compact('ciudades', 'idProveedor'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'prv_nombre'    => ['required', 'string', 'max:40'],
            'prv_ruc_ced'   => ['required', 'regex:/^\d{10}(\d{3})?$/'],
            'id_ciudad'     => ['required', 'string', 'size:3'],
            'prv_mail'      => ['required', 'email', 'max:60'],
            'prv_telefono'  => ['required', 'regex:/^\d{7,10}$/'],
            'prv_celular'   => ['nullable', 'regex:/^\d{10}$/'],
            'prv_direccion' => ['nullable', 'string', 'max:60'],
        ], [
            'prv_nombre.required' => config('mensajes.M16'),
            'prv_ruc_ced.regex'   => config('mensajes.M7'),
            'id_ciudad.required'  => config('mensajes.M11'),
            'prv_mail.email'      => config('mensajes.M23'),
            'prv_telefono.regex'  => config('mensajes.M21'),
            'prv_celular.regex'   => config('mensajes.M21'),
        ]);
        if (Proveedor::existeRuc($data['prv_ruc_ced'])) {
            return redirect()->route('proveedores.create')
                ->withInput()
                ->with($this->msg('M15', 'warning'));
        }
        try {
            Proveedor::registrar($data);
            return redirect()->route('proveedores.index')
                ->with($this->msg('M1', 'success'));
        } catch (\Throwable $e) {
            return redirect()->route('proveedores.create')
                ->withInput()
                ->with($this->msg('E12', 'danger'));
        }
    }
    public function edit($id)
    {
        return redirect()->route('proveedores.index', array_merge(request()->query(), [
            'edit' => trim((string) $id),
            'view' => null,
            'delete' => null
        ]));
    }
    public function update(Request $request, $id)
    {
        $id = trim((string) $id);
        $proveedor = Proveedor::findOrFail($id);

        if (trim((string) $proveedor->estado_prv) !== 'ACT') {
            return redirect()->route('proveedores.index')
                ->with($this->msg('M60', 'warning'));
        }
        $data = $request->validate([
            'prv_nombre'    => ['required', 'string', 'max:40'],
            'prv_ruc_ced'   => ['required', 'regex:/^\d{10}(\d{3})?$/'],
            'id_ciudad'     => ['required', 'string', 'size:3'],
            'prv_mail'      => ['required', 'email', 'max:60'],
            'prv_telefono'  => ['required', 'regex:/^\d{7,10}$/'],
            'prv_celular'   => ['nullable', 'regex:/^\d{10}$/'],
            'prv_direccion' => ['nullable', 'string', 'max:60'],
        ], [
            'prv_nombre.required' => config('mensajes.M16'),
            'prv_ruc_ced.regex'   => config('mensajes.M7'),
            'id_ciudad.required'  => config('mensajes.M11'),
            'prv_mail.email'      => config('mensajes.M23'),
            'prv_telefono.regex'  => config('mensajes.M21'),
            'prv_celular.regex'   => config('mensajes.M21'),
        ]);
        if (Proveedor::existeRuc($data['prv_ruc_ced'], $id)) {
            return redirect()->route('proveedores.index', array_merge(request()->query(), ['edit' => $id]))
                ->withInput()
                ->with($this->msg('M15', 'warning'));
        }
        try {
            $proveedor->aplicarCambios($data);

            return redirect()->route('proveedores.index')
                ->with($this->msg('M2', 'success'));
        } catch (\Throwable $e) {
            return redirect()->route('proveedores.index', array_merge(request()->query(), ['edit' => $id]))
                ->withInput()
                ->with($this->msg('E12', 'danger'));
        }
    }
    public function destroy($id)
    {
        $id = trim((string)$id);
        $proveedor = Proveedor::findOrFail($id);

        if (trim((string)$proveedor->estado_prv) !== 'ACT') {
            return redirect()->route('proveedores.index')
                ->with($this->msg('M60', 'warning'));
        }

        try {
            $proveedor->inactivar();

            return redirect()->route('proveedores.index')
                ->with($this->msg('M3', 'success'));
        } catch (\Throwable $e) {
            return redirect()->route('proveedores.index')
                ->with($this->msg('E12', 'danger'));
        }
    }
    // Mensajería de config/mensajes.php
    private function msg(string $codigo, string $tipo): array
    {
        return [
            'codigo_mensaje' => $codigo,
            'tipo_mensaje' => $tipo
        ];
    }
}
