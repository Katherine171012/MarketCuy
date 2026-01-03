<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UnidadMedida;
use Illuminate\Support\Facades\DB;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',
        'pro_descripcion',
        'pro_um_compra',
        'pro_um_venta',
        'pro_valor_compra',
        'pro_precio_venta',
        'pro_saldo_inicial',
        'pro_qty_ingresos',
        'pro_qty_egresos',
        'pro_qty_ajustes',
        'pro_saldo_final',
        'estado_prod',
        'pro_categoria',
    ];

    // =========================================================
    // RELACIONES
    // =========================================================
    public function unidadCompra()
    {
        return $this->belongsTo(
            UnidadMedida::class,
            'pro_um_compra',
            'id_unidad_medida'
        );
    }

    public function unidadVenta()
    {
        return $this->belongsTo(
            UnidadMedida::class,
            'pro_um_venta',
            'id_unidad_medida'
        );
    }

    // =========================================================
    // QUERIES (✅ Todo aquí, nada en Controller)
    // =========================================================

    /** Query base: solo ACT */
    public static function queryActivos()
    {
        return self::query()->whereIn('estado_prod', ['ACT', 'INA']);
    }

    /**
     * ✅ NUEVO (pedido jefa): método tipo obtenerParaLista($porPagina)
     * Ordena por estado y luego por ID numérico (P1000, P1001...)
     * Estados adaptados a tu módulo Productos:
     * ACT primero, INA después, PEN después, lo demás al final.
     */
    public static function obtenerParaLista(int $porPagina = 10)
    {
        return self::query()
            ->orderByRaw("CASE
                WHEN estado_prod = 'ACT' THEN 1
                WHEN estado_prod = 'INA' THEN 2
                ELSE 3 END")
            ->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC")
            ->paginate($porPagina);
    }

    /** Paginación por defecto (ACT + orden numérico) */
    public static function paginarActivos(int $perPage = 10)
    {
        return self::queryActivos()
            ->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC")
            ->paginate($perPage);
    }

    /** Buscar por ID para editar (solo wrapper, el controller no hace DB) */
    public static function buscarPorId(?string $id): ?self
    {
        if (!$id) return null;
        return self::find($id);
    }

    /** Paginación con filtros (orden, categoria, unidad) */
    public static function paginarActivosConFiltros(
        ?string $orden,
        ?string $categoria,
        ?string $unidad,
        int $perPage = 10
    ) {
        $query = self::queryActivos();

        if ($categoria !== null && $categoria !== '') {
            $query->where('pro_categoria', $categoria);
        }

        if ($unidad !== null && $unidad !== '') {
            $query->where('pro_um_compra', $unidad);
        }

        // default
        $orden = ($orden !== null && $orden !== '') ? $orden : 'id_asc';

        switch ($orden) {
            case 'id_asc':
                $query->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC");
                break;

            case 'id_desc':
                $query->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) DESC");
                break;

            case 'desc_az':
                $query->orderBy('pro_descripcion', 'ASC');
                break;

            case 'desc_za':
                $query->orderBy('pro_descripcion', 'DESC');
                break;

            default:
                // lo manejas en controller con mensaje
                return null;
        }

        return $query->paginate($perPage);
    }

    // =========================================================
    // VALIDACIONES / HELPERS
    // =========================================================
    public static function existeDescripcion(string $desc): bool
    {
        return self::where('pro_descripcion', $desc)->exists();
    }

    public static function existeId(string $id): bool
    {
        return self::where('id_producto', $id)->exists();
    }

    public static function generarSiguienteId(): string
    {
        $base = 1000;

        $max = (int) self::query()
            ->where('id_producto', 'like', 'P%')
            ->selectRaw("COALESCE(MAX(CAST(SUBSTRING(id_producto FROM 2) AS INTEGER)), 0) AS max_id")
            ->value('max_id');

        if ($max < ($base - 1)) {
            return 'P' . $base;
        }

        return 'P' . ($max + 1);
    }

    // =========================================================
    // CRUD INTERNO
    // =========================================================
    public static function crearProducto(array $data)
    {
        $idProducto = $data['id_producto'] ?? self::generarSiguienteId();

        return self::create([
            'id_producto'       => $idProducto,
            'pro_descripcion'   => $data['pro_descripcion'],
            'pro_um_compra'     => $data['unidad_medida'],
            'pro_um_venta'      => $data['unidad_medida'],
            'pro_valor_compra'  => $data['pro_valor_compra'] ?? 0,
            'pro_precio_venta'  => $data['pro_precio_venta'],
            'pro_saldo_inicial' => $data['pro_saldo_inicial'],

            'pro_qty_ingresos'  => 0,
            'pro_qty_egresos'   => 0,
            'pro_qty_ajustes'   => 0,

            'pro_saldo_final'   => $data['pro_saldo_inicial'],
            'estado_prod'       => 'ACT',
            'pro_categoria'     => $data['pro_categoria'] ?? null,
        ]);
    }

    public function actualizarProducto(array $data)
    {
        return $this->update([
            'pro_valor_compra'  => $data['pro_valor_compra'] ?? $this->pro_valor_compra,
            'pro_precio_venta'  => $data['pro_precio_venta'],
            'pro_saldo_inicial' => (int) $data['pro_saldo_inicial'],

            'pro_qty_ingresos'  => (int) $data['pro_qty_ingresos'],
            'pro_qty_egresos'   => (int) $data['pro_qty_egresos'],
            'pro_qty_ajustes'   => (int) $data['pro_qty_ajustes'],

            'pro_saldo_final'   => (int) $data['pro_saldo_final'],
            'pro_categoria'     => $data['pro_categoria'] ?? $this->pro_categoria,
        ]);
    }

    public function inactivarProducto()
    {
        return $this->update(['estado_prod' => 'INA']);
    }

    // =========================================================
    // TRANSACCIONES
    // =========================================================
    public static function crearProductoTx(array $data)
    {
        try {
            DB::beginTransaction();
            self::crearProducto($data);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizarProductoTx(array $data)
    {
        try {
            DB::beginTransaction();
            $this->actualizarProducto($data);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function inactivarProductoTx()
    {
        try {
            DB::beginTransaction();
            $this->inactivarProducto();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
