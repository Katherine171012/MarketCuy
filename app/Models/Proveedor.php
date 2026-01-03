<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    protected $table = 'proveedores';
    protected $primaryKey = 'id_proveedor';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id_proveedor',
        'prv_nombre',
        'prv_ruc_ced',
        'prv_telefono',
        'prv_mail',
        'id_ciudad',
        'prv_celular',
        'prv_direccion',
        'estado_prv',
        'fecha_ingreso',
    ];

    public function ciudad()
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    public static function generarNuevoId(): string
    {
        $ultimo = self::query()
            ->select('id_proveedor')
            ->orderBy('id_proveedor', 'desc')
            ->value('id_proveedor');

        $ultimo = trim((string) $ultimo);
        $num = 0;

        if ($ultimo !== '' && preg_match('/^PRV(\d{4})$/', $ultimo, $m)) {
            $num = (int) $m[1];
        }

        $num++;
        return 'PRV' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }
}
