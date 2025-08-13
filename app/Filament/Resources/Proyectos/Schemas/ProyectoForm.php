<?php

namespace App\Filament\Resources\Proyectos\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProyectoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nombre')
                    ->required(),
                TextInput::make('descripcion')
                    ->default(null),
                TextInput::make('cliente_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('fecha_inicio'),
                Select::make('estado')
                    ->options(['activo' => 'Activo', 'pausado' => 'Pausado', 'completado' => 'Completado'])
                    ->default('activo')
                    ->required(),
            ]);
    }
}
