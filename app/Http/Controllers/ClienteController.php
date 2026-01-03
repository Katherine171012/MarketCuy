<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Ciudad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $porPagina = $request->get('per_page', 10);

        try {
            $clientes = Cliente::obtenerParaLista($porPagina)->withQueryString();

            return view('clientes.index', compact('clientes', 'porPagina'));

        } catch (\Exception $e) {

            $clientes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $porPagina);

            return view('clientes.index', [
                'clientes' => $clientes,
                'porPagina' => $porPagina,
                'codigo_mensaje' => 'E12'
            ]);
        }
    }
    public function create()
    {
        $ciudades = Ciudad::all();
        return view('clientes.create', compact('ciudades'));
    }

    public function store(Request $request)
    {
        //VALIDACIONES DE IDENTIFICACIÓN
        if (!$request->filled('cli_ruc_ced')) {
            return back()->with('codigo_mensaje', 'M6')->withInput();
        }

        if (!is_numeric($request->cli_ruc_ced)) {
            return back()->with('codigo_mensaje', 'M8')->withInput();
        }

        $longitud = strlen($request->cli_ruc_ced);
        if ($longitud !== 10 && $longitud !== 13) {
            return back()->with('codigo_mensaje', 'M7')->withInput();
        }

        // VALIDACIÓN DE DUPLICADOS
        try {
            $dup = Cliente::consultarPorParametro('cli_ruc_ced', $request->cli_ruc_ced);
            if ($dup->isNotEmpty()) {
                return back()->with('codigo_mensaje', 'M10')->withInput();
            }
        } catch (\Exception $e) {
            return back()->with('codigo_mensaje', 'E1');
        }

        // 3. VALIDACIÓN DE CAMPOS OBLIGATORIOS
        if (!$request->filled('cli_nombre') ||
            !$request->filled('id_ciudad') ||
            !$request->filled('cli_celular') ||
            !$request->filled('cli_mail')) {
            return back()->with('codigo_mensaje', 'M11')->withInput();
        }

        if ($request->filled('cli_mail') && !filter_var($request->cli_mail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('codigo_mensaje', 'M23')->withInput();
        }

        DB::beginTransaction();
        try {
            $nuevoId = Cliente::generarSiguienteId();

            $datos = $request->all();
            $datos['id_cliente'] = $nuevoId;
            $datos['estado_cli'] = 'ACT';
            $datos['id_ciudad'] = trim($request->id_ciudad);

            Cliente::crearCliente($datos);

            DB::commit();
            return redirect()->route('clientes.index')->with('codigo_mensaje', 'M1');

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'E3')->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'E12')->withInput();
        }
    }

    public function edit(Cliente $cliente)
    {
        // Tu validación obligatoria
        if ($cliente->estado_cli !== 'ACT') {
            return redirect()->route('clientes.index')->with('codigo_mensaje', 'M60');
        }

        $ciudades = Ciudad::all();

        $porPagina = request('per_page', 10);
        $clientes = Cliente::obtenerParaLista($porPagina);

        return view('clientes.index', [
            'clientes' => $clientes,
            'clienteEdit' => $cliente,
            'ciudades' => $ciudades
        ]);
    }

    public function update(Request $request, Cliente $cliente)
    {
        // Validación de campos obligatorios
        if (!$request->filled('cli_nombre') || !$request->filled('id_ciudad') ||
            !$request->filled('cli_celular') || !$request->filled('cli_mail')) {
            return back()->with('codigo_mensaje', 'M11')->withInput();
        }

        // Validación de formato de correo
        if (!filter_var($request->cli_mail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('codigo_mensaje', 'M23')->withInput();
        }

        DB::beginTransaction();
        try {
            // Actualización excluyendo campos no editables (Regla F6.2)
            $datos = $request->except('cli_ruc_ced', 'id_cliente');
            $datos['id_ciudad'] = trim($request->id_ciudad);

            $cliente->actualizarCliente($datos);

            DB::commit();
            return redirect()->route('clientes.index')->with('codigo_mensaje', 'M2');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('codigo_mensaje', 'E3')->withInput();
        }
    }

    public function show(Cliente $cliente)
    {
        $porPagina = request('per_page', 10);
        $clientes = Cliente::obtenerParaLista($porPagina);

        return view('clientes.index', [
            'clientes' => $clientes,
            'clienteDelete' => $cliente
        ]);
    }

    public function destroy(Cliente $cliente)
    {
        DB::beginTransaction();
        try {
            $cliente->eliminarCliente();
            DB::commit();

            return redirect()->route('clientes.index')->with('codigo_mensaje', 'M3');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('clientes.index')->with('codigo_mensaje', 'E6');
        }
    }

    public function cancelarEliminacion()
    {
        return redirect()->route('clientes.index')->with('codigo_mensaje', 'M5');
    }

    public function buscarForm()
    {
        $porPagina = request('per_page', 10);
        $clientes = Cliente::obtenerParaLista($porPagina)->withQueryString();
        $ciudades = Ciudad::all();
        return view('clientes.index', [
            'clientes' => $clientes,
            'ciudades' => $ciudades,
            'busquedaActiva' => true
        ]);
    }
    public function buscar(Request $request)
    {
        $valor = ($request->campo === 'id_ciudad') ? $request->valor_ciudad : $request->valor_texto;

        if (!$request->filled('campo') || empty($valor)) {
            return back()->with('codigo_mensaje', 'M57');
        }

        try {
            $porPagina = $request->get('per_page', 10);

            $clientes = Cliente::consultarPorParametro($request->campo, $valor, $porPagina)->withQueryString();

            if ($clientes->isEmpty()) {
                return back()->with('codigo_mensaje', 'M59');
            }

            $ciudades = Ciudad::all();
            return view('clientes.resultados', compact('clientes', 'ciudades'));

        } catch (\Exception $e) {
            return back()->with('codigo_mensaje', 'E3');
        }
    }
    public function verDetalle(Cliente $cliente)
    {

        $porPagina = request('per_page', 10);
        $clientes = Cliente::obtenerParaLista($porPagina)->withQueryString();

        $ciudades = Ciudad::all();

        return view('clientes.index', [
            'clientes' => $clientes,
            'clienteDetalle' => $cliente,
            'ciudades' => $ciudades
        ]);
    }

}
