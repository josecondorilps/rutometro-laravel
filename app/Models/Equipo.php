<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    protected $fillable = [
        'identificador',
        'latitud',
        'longitud',
        'altitud',
        'tipo',
        'direccion',
        'area',
        'estado',
        'ruta_id',
        'orden_en_ruta',
        'qr_code_path',
        'inspeccionado',
        'fecha_inspeccion',
        'inspeccionado_por',
        'observaciones_campo'
    ];

    protected $casts = [
        'latitud' => 'decimal:8',
        'longitud' => 'decimal:8',
        'altitud' => 'decimal:3',
        'inspeccionado' => 'boolean',
        'fecha_inspeccion' => 'datetime'
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspeccionado_por');
    }

    public function fotos()
    {
        return $this->hasMany(FotoEquipo::class);
    }
}
