<?php

namespace App\Filament\Resources\Equipos\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EquipoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('identificador')
                    ->required(),
                TextInput::make('latitud')
                    ->required()
                    ->numeric(),
                TextInput::make('longitud')
                    ->required()
                    ->numeric(),
                TextInput::make('direccion')
                    ->default(null),
                Textarea::make('observaciones')
                    ->default(null)
                    ->columnSpanFull(),
                TextInput::make('area')
                    ->default(null),
                TextInput::make('estado')
                    ->default(null),
                TextInput::make('ruta_id')
                    ->numeric()
                    ->default(null),
                TextInput::make('proyecto_id')
                    ->required()
                    ->numeric(),
                TextInput::make('orden_en_ruta')
                    ->numeric()
                    ->default(null),
                TextInput::make('qr_code_path')
                    ->default(null),
                Toggle::make('inspeccionado')
                    ->required(),
                DateTimePicker::make('fecha_inspeccion'),
                TextInput::make('inspeccionado_por')
                    ->numeric()
                    ->default(null),
                Textarea::make('observaciones_campo')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
