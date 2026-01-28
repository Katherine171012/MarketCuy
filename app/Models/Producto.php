<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\UnidadMedida;
use App\Models\Categoria;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_producto',

        'pro_nombre',
        'pro_descripcion',

        'pro_um_compra',
        'pro_um_venta',
        'pro_valor_compra',
        'pro_precio_venta',

        'pro_precio_antes',
        'pro_etiqueta',
        'pro_es_destacado',
        'pro_clicks_count',

        'pro_saldo_inicial',
        'pro_qty_ingresos',
        'pro_qty_egresos',
        'pro_qty_ajustes',
        'pro_saldo_final',

        'estado_prod',
        'id_categoria',
        'pro_imagen',
    ];

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

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public static function obtenerActivos()
    {
        return self::where('estado_prod', 'ACT')
            ->with(['categoria', 'unidadCompra', 'unidadVenta'])
            ->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC")
            ->get();
    }

    public static function queryActivos()
    {
        return self::query()->whereIn('estado_prod', ['ACT', 'INA']);
    }

    public static function obtenerParaLista(int $porPagina = 10)
    {
        return self::query()
            ->with(['categoria', 'unidadCompra', 'unidadVenta'])
            ->orderByRaw("CASE
                WHEN estado_prod = 'ACT' THEN 1
                WHEN estado_prod = 'INA' THEN 2
                ELSE 3 END")
            ->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC")
            ->paginate($porPagina);
    }

    public static function paginarActivos(int $perPage = 10)
    {
        return self::queryActivos()
            ->with(['categoria', 'unidadCompra', 'unidadVenta'])
            ->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC")
            ->paginate($perPage);
    }

    public static function buscarPorId(?string $id): ?self
    {
        if (!$id) {
            return null;
        }
        return self::find($id);
    }

    public static function paginarActivosConFiltros(
        ?string $orden,
        ?string $categoria,
        ?string $unidad,
        int $perPage = 10
    ) {
        $query = self::queryActivos()
            ->with(['categoria', 'unidadCompra', 'unidadVenta']);

        if ($categoria !== null && $categoria !== '') {
            $query->where('id_categoria', (int) $categoria);
        }

        if ($unidad !== null && $unidad !== '') {
            $query->where('pro_um_compra', $unidad);
        }

        $orden = ($orden !== null && $orden !== '') ? $orden : 'id_asc';

        switch ($orden) {
            case 'id_asc':
                $query->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) ASC");
                break;

            case 'id_desc':
                $query->orderByRaw("CAST(SUBSTRING(id_producto FROM 2) AS INTEGER) DESC");
                break;

            case 'desc_az':
                $query->orderBy('pro_nombre', 'ASC');
                break;

            case 'desc_za':
                $query->orderBy('pro_nombre', 'DESC');
                break;

            default:
                return null;
        }

        return $query->paginate($perPage);
    }

    public static function existeNombre(string $nombre): bool
    {
        return self::whereRaw('LOWER(pro_nombre) = ?', [mb_strtolower(trim($nombre))])->exists();
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

    public static function crearProducto(array $data)
    {
        $idProducto = $data['id_producto'] ?? self::generarSiguienteId();

        $um = $data['unidad_medida'] ?? $data['pro_um_compra'] ?? $data['pro_um_venta'] ?? null;

        return self::create([
            'id_producto' => $idProducto,

            'pro_nombre' => $data['pro_nombre'],
            'pro_descripcion' => $data['pro_descripcion'] ?? null,

            'pro_um_compra' => $um,
            'pro_um_venta' => $um,
            'pro_valor_compra' => $data['pro_valor_compra'] ?? 0,
            'pro_precio_venta' => $data['pro_precio_venta'],

            'pro_precio_antes' => $data['pro_precio_antes'] ?? null,
            'pro_etiqueta' => $data['pro_etiqueta'] ?? null,
            'pro_es_destacado' => (bool) ($data['pro_es_destacado'] ?? false),
            'pro_clicks_count' => (int) ($data['pro_clicks_count'] ?? 0),

            'pro_saldo_inicial' => $data['pro_saldo_inicial'],
            'pro_qty_ingresos' => 0,
            'pro_qty_egresos' => 0,
            'pro_qty_ajustes' => 0,
            'pro_saldo_final' => $data['pro_saldo_inicial'],

            'estado_prod' => 'ACT',
            'id_categoria' => $data['id_categoria'] ?? null,
            'pro_imagen' => $data['pro_imagen'] ?? null,
        ]);
    }

    public function actualizarProducto(array $data)
    {
        return $this->update([
            'pro_descripcion' => array_key_exists('pro_descripcion', $data)
                ? ($data['pro_descripcion'] !== '' ? $data['pro_descripcion'] : null)
                : $this->pro_descripcion,
            'pro_um_venta' => $data['pro_um_venta'] ?? $this->pro_um_venta,
            'pro_um_compra' => $data['pro_um_compra'] ?? $this->pro_um_compra,
            'pro_valor_compra' => $data['pro_valor_compra'] ?? $this->pro_valor_compra,
            'pro_precio_venta' => $data['pro_precio_venta'],

            'pro_precio_antes' => array_key_exists('pro_precio_antes', $data)
                ? ($data['pro_precio_antes'] === '' ? null : $data['pro_precio_antes'])
                : $this->pro_precio_antes,

            'pro_etiqueta' => array_key_exists('pro_etiqueta', $data)
                ? ($data['pro_etiqueta'] === '' ? null : $data['pro_etiqueta'])
                : $this->pro_etiqueta,

            'pro_es_destacado' => (bool) ($data['pro_es_destacado'] ?? false),

            'pro_saldo_inicial' => (int) ($data['pro_saldo_inicial'] ?? $this->pro_saldo_inicial),
            'pro_qty_ingresos' => (int) ($data['pro_qty_ingresos'] ?? $this->pro_qty_ingresos),
            'pro_qty_egresos' => (int) ($data['pro_qty_egresos'] ?? $this->pro_qty_egresos),
            'pro_qty_ajustes' => (int) ($data['pro_qty_ajustes'] ?? $this->pro_qty_ajustes),
            'pro_saldo_final' => (int) ($data['pro_saldo_final'] ?? $this->pro_saldo_final),

            'id_categoria' => $data['id_categoria'] ?? $this->id_categoria,
            'pro_imagen' => $data['pro_imagen'] ?? $this->pro_imagen,
        ]);
    }

    public function inactivarProducto()
    {
        return $this->update(['estado_prod' => 'INA']);
    }

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

    public static function obtenerValoresCompraPorIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ids = array_map(fn($v) => trim((string) $v), $ids);

        $rows = self::query()
            ->selectRaw('TRIM(id_producto) as id_producto, pro_valor_compra')
            ->whereIn(DB::raw('TRIM(id_producto)'), $ids)
            ->where('estado_prod', 'ACT')
            ->get()
            ->keyBy('id_producto');

        $valores = [];

        foreach ($ids as $id) {
            if (!isset($rows[$id])) {
                throw new \Exception("Producto no encontrado o inactivo: {$id}");
            }

            $valor = $rows[$id]->pro_valor_compra;

            if ($valor === null) {
                throw new \Exception("Producto {$id} no tiene valor de compra");
            }

            $valores[] = (float) $valor;
        }

        return $valores;
    }

    public function getIdLimpio(): string
    {
        return trim((string) $this->id_producto);
    }

    public function getNombreLimpio(): string
    {
        return trim((string) $this->pro_nombre);
    }

    public function getEstadoTextoAttribute(): string
    {
        return $this->estado_prod === 'ACT' ? 'Activo' : 'Inactivo';
    }

    public function getEstadoClaseAttribute(): string
    {
        return $this->estado_prod === 'ACT' ? 'success' : 'secondary';
    }

    public function getEtiquetaTextoAttribute(): ?string
    {
        return $this->pro_etiqueta ?: null;
    }

    public function getEsOfertaAttribute(): bool
    {
        return mb_strtolower((string) $this->pro_etiqueta) === 'oferta'
            && $this->pro_precio_antes !== null;
    }

    public function getCategoriaTextoAttribute(): string
    {
        return $this->categoria?->cat_nombre ?? 'Sin categoría';
    }

    public function getImagenUrlAttribute(): string
    {
        if ($this->pro_imagen) {
            $img = ltrim((string) $this->pro_imagen, '/');

            if (file_exists(public_path('images/' . $img))) {
                return asset('images/' . $img);
            }

            if (Storage::disk('public')->exists($img)) {
                return asset('storage/' . $img);
            }
        }

        return asset('img/no-image.png');
    }

    public function getPuedeEditarAttribute(): bool
    {
        return $this->estado_prod === 'ACT';
    }

    public function getPuedeEliminarAttribute(): bool
    {
        return $this->estado_prod === 'ACT';
    }
}
