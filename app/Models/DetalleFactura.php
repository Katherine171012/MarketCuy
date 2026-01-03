<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleFactura extends Model
{
    protected $table = 'proxfac';
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

    protected $fillable = [
        'id_factura',
        'id_producto',
        'pxf_cantidad',
        'pxf_precio',
        'pxf_subtotal',
        'estado_pxf'
    ];

    /* ======================
     * RELACIONES
     * ====================== */

    public function producto()
    {
        return $this->belongsTo(
            Producto::class,
            'id_producto',
            'id_producto'
        );
    }

    /* ======================
     * LÓGICA (apoyo a Factura)
     * ====================== */

    /**
     * Obtener detalles activos de una factura
     */
    public static function obtenerPorFactura(string $idFactura)
    {
        return self::where('id_factura', $idFactura)
            ->where('estado_pxf', 'ACT')
            ->get();
    }

    /**
     * Crear un detalle de factura
     */
    public static function crearDetalle(
        string $idFactura,
        string $idProducto,
        int $cantidad,
        float $precio
    ) {
        return self::create([
            'id_factura'   => $idFactura,
            'id_producto'  => $idProducto,
            'pxf_cantidad' => $cantidad,
            'pxf_precio'   => $precio,
            'pxf_subtotal' => $cantidad * $precio,
            'estado_pxf'   => 'ACT'
        ]);
    }

    /**
     * Eliminar (o inactivar) detalles de una factura
     */
    public static function eliminarPorFactura(string $idFactura)
    {
        return self::where('id_factura', $idFactura)->delete();
        // si fuera lógico:
        // ->update(['estado_pxf' => 'INA']);
    }
}
