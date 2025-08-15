<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'panorama_filename',
        'panorama_thumbnail',
        'panorama_description',
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

    public function ruta(): BelongsTo
    {
        return $this->belongsTo(Ruta::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspeccionado_por');
    }

    // Método para convertir a formato de panorama para JavaScript
    public function toPanoramaArray(): array
    {
        return [
            'id' => $this->id,
            'latitude' => (float) $this->latitud,
            'longitude' => (float) $this->longitud,
            'filename' => $this->panorama_filename ?? "equipo_{$this->identificador}_360.jpg",
            'address' => $this->direccion ?? 'Dirección no especificada',
            'thumbnail' => $this->panorama_thumbnail ?? $this->generateDefaultThumbnail(),
            'identificador' => $this->identificador,
            'tipo' => $this->tipo,
            'estado' => $this->estado,
            'ruta_nombre' => $this->ruta?->nombre
        ];
    }

    private function generateDefaultThumbnail(): string
    {
        $colors = ['2196F3', '4CAF50', 'FF9800', '9C27B0', 'F44336', 'E91E63', '00BCD4'];
        $color = $colors[abs(crc32($this->identificador)) % count($colors)];
        $text = urlencode($this->identificador);

        return "https://via.placeholder.com/200x120/{$color}/white?text={$text}";
    }
}
