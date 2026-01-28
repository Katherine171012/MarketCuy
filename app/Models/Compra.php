<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Compra extends Model
{
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_proveedor',
        'oc_fecha_hora',
        'oc_subtotal',
        'oc_iva',
        'oc_total',
        'estado_oc',
        'estado_pag',
    ];

    public static function buscar(?string $parametro, string $valor, string $orden)
    {
        $query = self::query();

        if ($valor !== '' && $parametro) {
            if ($parametro === 'id_compra') {
                $query->where('id_compra', 'like', '%' . $valor . '%');
            } elseif ($parametro === 'id_proveedor') {
                $query->where('id_proveedor', 'like', '%' . $valor . '%');
            } elseif ($parametro === 'estado_oc') {
                $query->where('estado_oc', $valor);
            }
        }

        // 1) Primero por estado: ABI -> APR -> ANU (de arriba hacia abajo)
        $query->orderByRaw("
        CASE TRIM(estado_oc)
            WHEN 'APR' THEN 1
            WHEN 'ABI' THEN 2
            WHEN 'ANU' THEN 3
            ELSE 4
        END ASC
    ");

        // 2) Luego por el campo que el usuario selecciona en "Ordenar por"
        $campoOrden = match ($orden) {
            'fecha' => 'oc_fecha_hora',
            'estado' => 'estado_oc',
            'proveedor' => 'id_proveedor',
            default => 'oc_fecha_hora',
        };

        // Dirección: para que se vea "de arriba hacia abajo" (más reciente arriba en fecha)
        $direccion = 'desc';

        $query->orderBy($campoOrden, $direccion);

        return $query->paginate(10);
    }


    public static function obtenerPorId(string $id): ?self
    {
        return self::query()->where('id_compra', $id)->first();
    }

    public static function generarIdSugerido(): string
    {
        $max = self::query()
            ->selectRaw("COALESCE(MAX(substring(id_compra from 4)::integer), 0) as maximo")
            ->value('maximo');

        $nuevo = ((int) $max) + 1;
        return 'OC-' . str_pad((string) $nuevo, 5, '0', STR_PAD_LEFT);
    }

    public static function spCrear(string $idProveedor, $fecha, array $productos, array $cantidades, array $valores): string
    {
        $pgProductos = self::pgArrayText($productos);
        $pgCantidades = self::pgArrayInt($cantidades);
        $pgValores = self::pgArrayNumeric($valores);

        $row = DB::selectOne(
            "SELECT public.sp_oc_crear(?, ?::timestamp, ?::text[], ?::integer[], ?::numeric[]) AS id_oc",
            [$idProveedor, $fecha, $pgProductos, $pgCantidades, $pgValores]
        );

        return $row->id_oc ?? '';
    }

    private static function pgArrayText(array $arr): string
    {
        $arr = array_map(function ($v) {
            $v = trim((string) $v);
            $v = str_replace(['\\', '"'], ['\\\\', '\\"'], $v);
            return '"' . $v . '"';
        }, $arr);

        return '{' . implode(',', $arr) . '}';
    }

    private static function pgArrayInt(array $arr): string
    {
        $arr = array_map(fn($v) => (int) $v, $arr);
        return '{' . implode(',', $arr) . '}';
    }

    private static function pgArrayNumeric(array $arr): string
    {
        $arr = array_map(function ($v) {
            $n = is_null($v) ? 0 : (float) $v;
            return rtrim(rtrim(number_format($n, 4, '.', ''), '0'), '.');
        }, $arr);

        return '{' . implode(',', $arr) . '}';
    }

    public static function spActualizar(string $idCompra, string $idProveedor, $fechaIgnorada, array $productos, array $cantidades, array $valores): string
    {
        $pgProductos = self::pgArrayText($productos);
        $pgCantidades = self::pgArrayInt($cantidades);
        $pgValores = self::pgArrayNumeric($valores);

        $row = DB::selectOne(
            "SELECT public.sp_oc_actualizar(?, ?, ?::text[], ?::integer[], ?::numeric[]) AS msg",
            [$idCompra, $idProveedor, $pgProductos, $pgCantidades, $pgValores]
        );

        return $row->msg ?? '';
    }

    public static function spAprobar(string $idCompra): void
    {
        DB::selectOne("SELECT public.sp_oc_aprobar(?)", [$idCompra]);
    }

    public static function spAnular(string $idCompra): string
    {
        $sql = "SELECT public.sp_oc_anular(?) AS mensaje";
        $res = DB::selectOne($sql, [$idCompra]);
        return (string) ($res->mensaje ?? '');
    }

    // ==========================================
    // Métodos helper para limpiar lógica de vistas
    // ==========================================

    /**
     * Determina si la orden de compra está en estado Abierta
     */
    public function esAbierta(): bool
    {
        return trim($this->estado_oc) === 'ABI';
    }

    /**
     * Determina si la orden de compra está Aprobada
     */
    public function esAprobada(): bool
    {
        return trim($this->estado_oc) === 'APR';
    }

    /**
     * Determina si la orden de compra está Anulada
     */
    public function esAnulada(): bool
    {
        return trim($this->estado_oc) === 'ANU';
    }

    /**
     * Obtiene el ID de la compra limpio (sin espacios)
     */
    public function getIdLimpio(): string
    {
        return trim((string) $this->id_compra);
    }

    /**
     * Obtiene el ID del proveedor limpio (sin espacios)
     */
    public function getIdProveedorLimpio(): string
    {
        return trim((string) $this->id_proveedor);
    }
}
