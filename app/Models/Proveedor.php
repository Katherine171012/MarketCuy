<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

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
    // Genera PRV....
    public static function generarNuevoId(): string
    {
        $maxNum = self::query()
            ->selectRaw("MAX(CAST(SUBSTRING(TRIM(id_proveedor), 4, 4) AS INTEGER)) AS maxnum")
            ->value('maxnum');

        $num = (int) ($maxNum ?? 0);
        $num++;

        return 'PRV' . str_pad((string) $num, 4, '0', STR_PAD_LEFT);
    }
    // Verifica duplicidad de RUC/Cédula
    public static function existeRuc(string $ruc, ?string $excluirId = null): bool
    {
        $ruc = trim($ruc);

        $q = self::query()
            ->whereRaw('TRIM(prv_ruc_ced) = ?', [$ruc]);

        if ($excluirId !== null && trim($excluirId) !== '') {
            $q->whereRaw('TRIM(id_proveedor) <> ?', [trim($excluirId)]);
        }

        return $q->exists();
    }
    // Construye la consulta de index
    public static function construirQueryIndex(?string $parametro, string $valor, ?string $orden)
    {
        $valor = trim((string) $valor);
        $parametro = $parametro ? trim($parametro) : null;
        $orden = $orden ? trim($orden) : null;

        $query = self::query()
            ->with('ciudad')
            // ACT primero
            ->orderByRaw("CASE WHEN TRIM(estado_prv) = 'ACT' THEN 0 ELSE 1 END")
            ->orderBy('id_proveedor');
        // Filtro por parámetro
        if ($parametro && $valor !== '') {
            if ($parametro === 'nombre') {
                $query->where('prv_nombre', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'ruc') {
                $query->where('prv_ruc_ced', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'correo') {
                $query->where('prv_mail', 'ILIKE', "%{$valor}%");
            } elseif ($parametro === 'estado') {
                $query->whereRaw('TRIM(estado_prv) = ?', [strtoupper($valor)]);
            } elseif ($parametro === 'ciudad') {
                $query->whereHas('ciudad', function ($q) use ($valor) {
                    $q->where('ciu_descripcion', 'ILIKE', "%{$valor}%");
                });
            }
        }

        if ($orden === 'nombre') {
            $query->orderBy('prv_nombre');
        } elseif ($orden === 'estado') {
            $query->orderByRaw("TRIM(estado_prv) ASC");
        }

        return $query;
    }
    // Crea proveedor con transacción usando ID mas estado ACT mas fecha actual)
    public static function registrar(array $data): self
    {
        return DB::transaction(function () use ($data) {
            $nuevoId = self::generarNuevoId();

            return self::create([
                'id_proveedor'  => $nuevoId,
                'prv_nombre'    => $data['prv_nombre'],
                'prv_ruc_ced'   => $data['prv_ruc_ced'],
                'id_ciudad'     => $data['id_ciudad'],
                'prv_mail'      => $data['prv_mail'],
                'prv_telefono'  => $data['prv_telefono'],
                'prv_celular'   => $data['prv_celular'] ?? null,
                'prv_direccion' => $data['prv_direccion'] ?? null,
                'estado_prv'    => 'ACT',
                'fecha_ingreso' => now()->toDateString(),
            ]);
        });
    }
    // Actualiza proveedor con transacción solo campos editables
    public function aplicarCambios(array $data): void
    {
        DB::transaction(function () use ($data) {
            $this->update([
                'prv_nombre'    => $data['prv_nombre'],
                'prv_ruc_ced'   => $data['prv_ruc_ced'],
                'id_ciudad'     => $data['id_ciudad'],
                'prv_mail'      => $data['prv_mail'],
                'prv_telefono'  => $data['prv_telefono'],
                'prv_celular'   => $data['prv_celular'] ?? null,
                'prv_direccion' => $data['prv_direccion'] ?? null,
            ]);
        });
    }
    // Eliminado logico
    public function inactivar(): void
    {
        DB::transaction(function () {
            $this->update(['estado_prv' => 'INA']);
        });
    }
}
