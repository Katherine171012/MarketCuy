<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetalleFactura extends Model
{
    protected $table = 'proxfac';

    // Clave primaria compuesta (id_factura + id_producto)
    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = ['id_factura', 'id_producto'];

    protected $fillable = [
        'id_factura',
        'id_producto',
        'pxf_cantidad',
        'pxf_precio',
        'pxf_subtotal',
        'estado_pxf',
        'sync_updated_at'
    ];

    /* ======================
     * RELACIONES
     * ====================== */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto', 'id_producto');
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
        // Validaciones previas antes de insertar en DB
        if (empty($idFactura) || empty($idProducto) || $cantidad <= 0 || $precio <= 0) {
            throw new \Exception('Datos inválidos para insertar detalle');
        }

        // Validación de existencia de producto y factura
        $producto = Producto::find($idProducto);
        if (!$producto || $producto->estado_prod !== 'ACT') {
            throw new \Exception('Producto no válido o no disponible');
        }

        $factura = Factura::find($idFactura);
        if (!$factura || $factura->estado_fac !== 'ABI') {
            throw new \Exception('Factura no válida o no editable');
        }

        // Inserción en la tabla de detalles
        return DB::table('proxfac')->insert([
            'id_factura'      => $idFactura,
            'id_producto'     => $idProducto,
            'pxf_cantidad'    => $cantidad,
            'pxf_precio'      => $precio,
            'pxf_subtotal'    => $cantidad * $precio,
            'estado_pxf'      => 'ACT',
            'sync_updated_at' => now(),
        ]);
    }

    /**
     * Eliminar (o inactivar) detalles de una factura
     */
    public static function eliminarPorFactura(string $idFactura)
    {
        // Puedes eliminar detalles por factura o realizar una inactivación lógica
        return self::where('id_factura', $idFactura)->delete();  // Aquí es un delete físico
    }
}
