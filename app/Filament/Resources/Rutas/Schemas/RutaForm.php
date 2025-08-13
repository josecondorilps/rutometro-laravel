<?php

namespace App\Filament\Resources\Rutas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RutaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('proyecto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('total_equipos')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('distancia_km')
                    ->numeric()
                    ->default(null),
                TextInput::make('tiempo_estimado_minutos')
                    ->numeric()
                    ->default(null),
                TextInput::make('centro_lat')
                    ->numeric()
                    ->default(null),
                TextInput::make('centro_lng')
                    ->numeric()
                    ->default(null),
                Select::make('estado')
                    ->options([
            'generada' => 'Generada',
            'asignada' => 'Asignada',
            'en_progreso' => 'En progreso',
            'completada' => 'Completada',
        ])
                    ->default('generada')
                    ->required(),
                TextInput::make('asignado_a')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('fecha_asignacion'),
            ]);
    }
}
