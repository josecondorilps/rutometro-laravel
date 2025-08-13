<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isLpsAdmin(): bool
    {
        return $this->hasRole('lps_admin');
    }

    public function isLpsCampo(): bool
    {
        return $this->hasRole('lps_campo');
    }

    public function canAccessAdminPanel(): bool
    {
        return $this->hasRole('super_admin') || $this->hasRole('lps_admin');
    }

    public function canAccessCampoPanel(): bool
    {
        return $this->hasRole('lps_campo');
    }


    // RELACIONES ADMIN
    public function rutasAsignadas()
    {
        return $this->hasMany(Ruta::class, 'asignado_a');
    }

    public function equiposInspeccionados()
    {
        return $this->hasMany(Equipo::class, 'inspeccionado_por');
    }

    public function fotos()
    {
        return $this->hasMany(FotoEquipo::class);
    }
}
