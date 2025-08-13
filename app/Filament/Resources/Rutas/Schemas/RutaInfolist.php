<?php

namespace App\Filament\Resources\Rutas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RutaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('proyecto_id')
                    ->numeric(),
                TextEntry::make('total_equipos')
                    ->numeric(),
                TextEntry::make('distancia_km')
                    ->numeric(),
                TextEntry::make('tiempo_estimado_minutos')
                    ->numeric(),
                TextEntry::make('centro_lat')
                    ->numeric(),
                TextEntry::make('centro_lng')
                    ->numeric(),
                TextEntry::make('estado'),
                TextEntry::make('asignado_a')
                    ->numeric(),
                TextEntry::make('fecha_asignacion')
                    ->dateTime(),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
