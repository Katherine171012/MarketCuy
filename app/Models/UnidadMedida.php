<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medidas';
    protected $primaryKey = 'id_unidad_medida';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_unidad_medida'
        // si tu tabla tiene más columnas, agrégalas aquí
    ];

    // ✅ Query encapsulada (para que controller no consulte)
    public static function listar()
    {
        return self::orderBy('id_unidad_medida')->get();
    }
}
