<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proyecto extends Model
{
    protected $fillable = [
        'nombre',
        'descripcion',
        'cliente_id',
        'fecha_inicio',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function rutas()
    {
        return $this->hasMany(Ruta::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}
