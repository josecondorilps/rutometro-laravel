<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $fillable = [
        'nombre',
        'email',
        'telefono',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    public function proyectos()
    {
        return $this->hasMany(Proyecto::class);
    }
}
