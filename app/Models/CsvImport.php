<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvImport extends Model
{
    protected $fillable = [
        'file_path',
        'proyecto_id',
        'user_id',
        'status',
        'stats',
        'error_message',
        'started_at',
        'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'stats' => 'array'
    ];

    public function proyecto()
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
