<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MisRutas extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ruta_id',
        'nombre',
        'estado',
        'fecha_asignacion',
        'fecha_inicio',
        'fecha_completado',
        'notas',
    ];

    protected $casts = [
        'fecha_asignacion' => 'datetime',
        'fecha_inicio' => 'datetime',
        'fecha_completado' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class);
    }
}
