<?php

namespace App\Filament\Resources\Equipos\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EquipoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('identificador'),
                TextEntry::make('latitud')
                    ->numeric(),
                TextEntry::make('longitud')
                    ->numeric(),
                TextEntry::make('direccion'),
                TextEntry::make('area'),
                TextEntry::make('estado'),
                TextEntry::make('ruta_id')
                    ->numeric(),
                TextEntry::make('proyecto_id')
                    ->numeric(),
                TextEntry::make('orden_en_ruta')
                    ->numeric(),
                TextEntry::make('qr_code_path'),
                IconEntry::make('inspeccionado')
                    ->boolean(),
                TextEntry::make('fecha_inspeccion')
                    ->dateTime(),
                TextEntry::make('inspeccionado_por')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
