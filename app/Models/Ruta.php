<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruta extends Model
{
    protected $fillable = [
        'nombre',
        'proyecto_id',
        'total_equipos',
        'distancia_km',
        'tiempo_estimado_minutos',
        'centro_lat',
        'centro_lng',
        'estado',
        'asignado_a',
        'fecha_asignacion'
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'centro_lat' => 'decimal:8',
        'centro_lng' => 'decimal:8',
        'distancia_km' => 'decimal:3'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function usuarioAsignado()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }

    public function clusters()
    {
        return $this->hasMany(Cluster::class);
    }
}
