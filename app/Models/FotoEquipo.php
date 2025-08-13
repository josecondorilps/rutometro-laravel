<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoEquipo extends Model
{
    protected $fillable = [
        'equipo_id',
        'user_id',
        'ruta_archivo',
        'nombre_archivo',
        'metadata',
        'latitud_foto',
        'longitud_foto'
    ];

    protected $casts = [
        'metadata' => 'array',
        'latitud_foto' => 'decimal:8',
        'longitud_foto' => 'decimal:8'
    ];

    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
