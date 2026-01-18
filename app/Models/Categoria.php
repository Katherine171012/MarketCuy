<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'cat_nombre',
        'estado_cat',
    ];

    public static function listarActivas()
    {
        return self::where('estado_cat', 'ACT')
            ->orderBy('cat_nombre', 'ASC')
            ->get();
    }

    public static function obtenerParaHome(int $limite = 8)
    {
        return self::where('estado_cat', 'ACT')
            ->orderBy('id_categoria', 'asc')
            ->limit($limite)
            ->get();
    }
}
