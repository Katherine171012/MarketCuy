<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        'unidad_medida',
        'pro_valor_compra',
        'pro_precio_venta',
        'pro_precio_antes',
        'pro_saldo_inicial',
        'pro_qty_ingresos',
        'pro_qty_egresos',
        'pro_qty_ajustes',
        'pro_saldo_final',
        'id_categoria',
        'pro_etiqueta',
        'pro_es_destacado',
        'pro_clicks_count',
        'pro_imagen',
        'estado_prod',
    ];

    /* =========================
       RELACIONES
    ========================= */

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id_categoria');
    }

    public function unidad()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida', 'id_unidad_medida');
    }

    /* =========================
       ACCESSORS (VISTA)
    ========================= */

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
        if ($this->pro_imagen && Storage::disk('public')->exists($this->pro_imagen)) {
            return asset('storage/' . $this->pro_imagen);
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

    /* =========================
       QUERIES
    ========================= */

    public static function buscarPorId(string $id): ?self
    {
        return self::where('id_producto', $id)->first();
    }

    public static function existeNombre(string $nombre): bool
    {
        return self::whereRaw('LOWER(pro_nombre) = ?', [mb_strtolower($nombre)])
            ->exists();
    }

    public static function obtenerParaLista(int $perPage)
    {
        return self::with(['categoria', 'unidad'])
            ->orderBy('id_producto', 'ASC')
            ->paginate($perPage);
    }

    public static function paginarActivosConFiltros($orden, $categoria, $unidad, int $perPage)
    {
        $q = self::with(['categoria', 'unidad'])
            ->where('estado_prod', 'ACT');

        if ($categoria) {
            $q->where('id_categoria', $categoria);
        }

        if ($unidad) {
            $q->where('unidad_medida', $unidad);
        }

        if ($orden) {
            match ($orden) {
                'precio_asc'  => $q->orderBy('pro_precio_venta', 'ASC'),
                'precio_desc' => $q->orderBy('pro_precio_venta', 'DESC'),
                'nombre_asc'  => $q->orderBy('pro_nombre', 'ASC'),
                'nombre_desc' => $q->orderBy('pro_nombre', 'DESC'),
                default       => null,
            };
        }

        return $q->paginate($perPage);
    }

    /* =========================
       TRANSACCIONES
    ========================= */

    public static function generarSiguienteId(): string
    {
        $ultimo = self::orderBy('id_producto', 'DESC')->first();

        if (!$ultimo) {
            return 'P001';
        }

        $num = (int) substr($ultimo->id_producto, 1) + 1;
        return 'P' . str_pad((string) $num, 3, '0', STR_PAD_LEFT);
    }

    public static function crearProductoTx(array $data): void
    {
        DB::transaction(function () use ($data) {
            self::create($data);
        });
    }

    public function actualizarProductoTx(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->update($data);
        });
    }

    public function inactivarProductoTx(): void
    {
        DB::transaction(function () {
            $this->update(['estado_prod' => 'INA']);
        });
    }
}
