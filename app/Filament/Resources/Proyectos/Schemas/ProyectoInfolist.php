<?php

namespace App\Filament\Resources\Proyectos\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ProyectoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nombre'),
                TextEntry::make('descripcion'),
                TextEntry::make('cliente_id')
                    ->numeric(),
                TextEntry::make('fecha_inicio')
                    ->date(),
                TextEntry::make('estado'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
