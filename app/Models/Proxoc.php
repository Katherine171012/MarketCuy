<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Proxoc extends Model
{
    protected $table = 'proxoc';
    public $timestamps = false;

    protected $fillable = [
        'id_compra',
        'id_producto',
        'pxo_cantidad',
        'pxo_valor',
        'pxo_subtotal',
        'estado_pxoc',
    ];

    public static function obtenerPorCompra(string $idCompra)
    {
        return self::query()
            ->where('id_compra', $idCompra)
            ->orderBy('id_producto')
            ->get();
    }

    public static function reemplazarDetalle(string $idCompra, array $productos, array $cantidades, array $valores): void
    {
        self::query()->where('id_compra', $idCompra)->delete();

        $data = [];
        for ($i = 0; $i < count($productos); $i++) {
            $cant = (int) $cantidades[$i];
            $val = (float) $valores[$i];

            $data[] = [
                'id_compra' => $idCompra,
                'id_producto' => $productos[$i],
                'pxo_cantidad' => $cant,
                'pxo_valor' => $val,
                'pxo_subtotal' => round($cant * $val, 2),
                'estado_pxoc' => 'ABI',
            ];
        }

        self::insert($data);
    }
}
