<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    protected $fillable = [
        'ruta_id',
        'numero_cluster',
        'centro_lat',
        'centro_lng',
        'total_equipos'
    ];

    protected $casts = [
        'centro_lat' => 'decimal:8',
        'centro_lng' => 'decimal:8'
    ];

    public function ruta()
    {
        return $this->belongsTo(Ruta::class);
    }

    public function equipos()
    {
        return $this->hasMany(Equipo::class);
    }
}
