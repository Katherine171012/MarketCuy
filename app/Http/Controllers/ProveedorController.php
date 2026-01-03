<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);

        $query = Proveedor::query()
            ->with('ciudad')
            ->orderBy('id_proveedor');

        // Consulta por parámetro (como ya lo tenías)
        $parametro = $request->get('parametro');
        $valor = trim((string) $request->get('valor', ''));

        if ($parametro && $valor !== '') {
            if ($parametro === 'nombre') {
                $query->where('prv_nombre', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'ruc') {
                $query->where('prv_ruc_ced', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'correo') {
                $query->where('prv_mail', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'estado') {
                $query->whereRaw('TRIM(estado_prv) = ?', [strtoupper($valor)]);
            } elseif ($parametro === 'ciudad') {
                $query->whereHas('ciudad', function ($q) use ($valor) {
                    $q->where('ciu_descripcion', 'ILIKE', "%{$valor}%");
                });
            }
        }

        // Orden (como ya lo tenías)
        $orden = $request->get('orden');
        if ($orden === 'nombre') $query->orderBy('prv_nombre');
        if ($orden === 'estado') $query->orderBy('estado_prv');

        $proveedores = $query->paginate($perPage)->appends($request->query());

        // Paneles: editar / visualizar / eliminar (modal) dentro del index
        $idEdit = trim((string) $request->get('edit', ''));
        $idView = trim((string) $request->get('view', ''));
        $idDel  = trim((string) $request->get('delete', ''));

        $proveedorEdit = $idEdit !== '' ? Proveedor::with('ciudad')->find($idEdit) : null;
        $proveedorView = $idView !== '' ? Proveedor::with('ciudad')->find($idView) : null;
        $proveedorDelete = $idDel !== '' ? Proveedor::find($idDel) : null;

        // Para dropdown de ciudad en edit/view y en create
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
            'prv_ruc_ced.regex' => 'RUC/Cédula debe tener 10 o 13 dígitos.',
            'prv_telefono.regex' => 'Teléfono inválido.',
            'prv_celular.regex' => 'Celular inválido.',
            'prv_nombre.required' => config('mensajes.M16'),
            'id_ciudad.required' => config('mensajes.M11'),
            'prv_mail.email'     => config('mensajes.M23'),

        ]);

        // Validación duplicados (RUC tiene índice UNIQUE en BD)
        $ruc = $data['prv_ruc_ced'];
        $yaExisteRuc = Proveedor::query()
            ->whereRaw('TRIM(prv_ruc_ced) = ?', [$ruc])
            ->exists();

        if ($yaExisteRuc) {
            return redirect()->route('proveedores.create')
                ->withInput()
                ->with($this->msg('M15', 'warning'));
        }

        try {
            DB::transaction(function () use ($data) {
                $nuevoId = Proveedor::generarNuevoId();

                Proveedor::create([
                    'id_proveedor'  => $nuevoId,
                    'prv_nombre'    => $data['prv_nombre'],
                    'prv_ruc_ced'   => $data['prv_ruc_ced'],
                    'id_ciudad'     => $data['id_ciudad'],
                    'prv_mail'      => $data['prv_mail'],
                    'prv_telefono'  => $data['prv_telefono'],
                    'prv_celular'   => $data['prv_celular'] ?? null,
                    'prv_direccion' => $data['prv_direccion'] ?? null,
                    'estado_prv'    => 'ACT',
                    'fecha_ingreso' => now()->toDateString(),
                ]);
            });

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
        // Para que el edit se muestre "arribita" en el index
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
        ]);

        // Duplicados RUC (ignorando el mismo proveedor)
        $ruc = $data['prv_ruc_ced'];
        $dupRuc = Proveedor::query()
            ->whereRaw('TRIM(prv_ruc_ced) = ?', [$ruc])
            ->whereRaw('TRIM(id_proveedor) <> ?', [$id])
            ->exists();

        if ($dupRuc) {
            return redirect()->route('proveedores.index', array_merge(request()->query(), ['edit' => $id]))
                ->withInput()
                ->with($this->msg('M15', 'warning'));
        }

        try {
            DB::transaction(function () use ($proveedor, $data) {
                $proveedor->update([
                    'prv_nombre'    => $data['prv_nombre'],
                    'prv_ruc_ced'   => $data['prv_ruc_ced'],
                    'id_ciudad'     => $data['id_ciudad'],
                    'prv_mail'      => $data['prv_mail'],
                    'prv_telefono'  => $data['prv_telefono'],
                    'prv_celular'   => $data['prv_celular'] ?? null,
                    'prv_direccion' => $data['prv_direccion'] ?? null,
                ]);
            });

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
        $id = trim((string) $id);
        $proveedor = Proveedor::findOrFail($id);

        if (trim((string) $proveedor->estado_prv) !== 'ACT') {
            return redirect()->route('proveedores.index')
                ->with($this->msg('M60', 'warning'));
        }

        try {
            DB::transaction(function () use ($proveedor) {
                $proveedor->update(['estado_prv' => 'INA']);
            });

            return redirect()->route('proveedores.index')
                ->with($this->msg('M3', 'success'));
        } catch (\Throwable $e) {
            return redirect()->route('proveedores.index')
                ->with($this->msg('E12', 'danger'));

        }
    }
    // Centraliza el envío de mensajes estándar
    private function msg(string $codigo, string $tipo)
    {
        return [
            'codigo_mensaje' => $codigo,
            'tipo_mensaje' => $tipo
        ];
    }

}
