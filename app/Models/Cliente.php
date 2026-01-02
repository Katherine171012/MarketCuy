<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';
    protected $primaryKey = 'id_cliente';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_cliente',
        'cli_nombre',
        'cli_ruc_ced',
        'cli_telefono',
        'cli_mail',
        'id_ciudad',
        'cli_celular',
        'cli_direccion',
        'estado_cli'
    ];


    //Obtener todos los clientes
    public static function obtenerTodos()
    {
        return self::orderBy('cli_nombre', 'asc')->get();
    }

    //Consulta por parámetro
    public static function consultarPorParametro($campo, $valor, $porPagina = 10)
    {
        $query = self::with('ciudad');

        if ($campo === 'id_ciudad') {
            // PostgreSQL: Comparamos el ID de ciudad quitando espacios en ambos lados
            $query->whereRaw("trim(id_ciudad) = ?", [trim($valor)]);
        } else {
            $query->where($campo, 'ILIKE', "%{$valor}%");
        }

        return $query->orderByRaw("CASE WHEN estado_cli = 'ACT' THEN 1 WHEN estado_cli = 'SUS' THEN 2 ELSE 3 END")
            ->paginate($porPagina);
    }
    //Crear un cliente
    public static function crearCliente(array $datos)
    {
        return self::create($datos);
    }
    public function ciudad() {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }
    public static function generarSiguienteId()
    {
        $ultimoId = self::where('id_cliente', 'LIKE', 'CLI%')
            ->selectRaw('MAX(CAST(SUBSTRING(id_cliente FROM 4) AS INTEGER)) as total')
            ->value('total');

        $siguiente = ($ultimoId ?? 0) + 1;

        return 'CLI' . str_pad($siguiente, 4, '0', STR_PAD_LEFT);
    }
    //Actualizar cliente
    public function actualizarCliente(array $datos)
    {
        return $this->update($datos);
    }

    //Eliminación lógica del cliente
    public function eliminarCliente()
    {
        return $this->update([
            'estado_cli' => 'INA'
        ]);
    }
    public static function obtenerParaLista($porPagina = 10)
    {
        return self::with('ciudad')
            ->orderByRaw("CASE
            WHEN estado_cli = 'ACT' THEN 1
            WHEN estado_cli = 'SUS' THEN 2
            ELSE 3 END")
            ->orderBy('id_cliente', 'asc')
            ->paginate($porPagina);
    }

}
