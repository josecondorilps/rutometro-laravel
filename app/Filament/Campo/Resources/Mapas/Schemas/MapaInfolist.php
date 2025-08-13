<?php
// app/Filament/Campo/Resources/Mapas/Schemas/MapaInfolist.php

namespace App\Filament\Campo\Resources\Mapas\Schemas;

use Filament\Infolists;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MapaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('🔧 Información del Equipo')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('identificador')
                                    ->label('Identificador')
                                    ->weight('bold')
                                    ->copyable()
                                    ->icon('heroicon-o-identification'),

                                Infolists\Components\TextEntry::make('tipo')
                                    ->label('Tipo')
                                    ->badge()
                                    ->formatStateUsing(fn (string $state): string => match($state) {
                                        'antena' => '📡 Antena',
                                        'repetidor' => '🔄 Repetidor',
                                        'torre' => '🗼 Torre',
                                        'gabinete' => '🏢 Gabinete',
                                        'router' => '🌐 Router',
                                        'switch' => '🔀 Switch',
                                        'servidor' => '💻 Servidor',
                                        'ups' => '🔋 UPS',
                                        'fibra_optica' => '🔌 Fibra',
                                        default => '⚙️ ' . ucfirst($state),
                                    }),

                                Infolists\Components\TextEntry::make('estado')
                                    ->label('Estado')
                                    ->badge()
                                    ->color(fn (string $state): string => match($state) {
                                        'operativo' => 'success',
                                        'mantenimiento' => 'warning',
                                        'fuera_servicio' => 'danger',
                                        'pendiente' => 'gray',
                                        default => 'primary',
                                    })
                                    ->formatStateUsing(fn (string $state): string => match($state) {
                                        'operativo' => '✅ Operativo',
                                        'mantenimiento' => '🔧 Mantenimiento',
                                        'fuera_servicio' => '❌ Fuera Servicio',
                                        'pendiente' => '⏳ Pendiente',
                                        default => ucfirst($state),
                                    }),
                            ]),

                        Infolists\Components\TextEntry::make('area')
                            ->label('📍 Área/Zona')
                            ->placeholder('No especificada'),
                    ]),

                Section::make('🗺️ Ubicación GPS')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('latitud')
                                    ->label('Latitud')
                                    ->copyable()
                                    ->icon('heroicon-o-globe-alt'),

                                Infolists\Components\TextEntry::make('longitud')
                                    ->label('Longitud')
                                    ->copyable()
                                    ->icon('heroicon-o-globe-alt'),

                                Infolists\Components\TextEntry::make('altitud')
                                    ->label('Altitud')
                                    ->suffix(' m.s.n.m.')
                                    ->placeholder('No especificada')
                                    ->icon('heroicon-o-arrow-trending-up'),
                            ]),

                        Infolists\Components\TextEntry::make('direccion')
                            ->label('📍 Dirección')
                            ->columnSpanFull()
                            ->placeholder('No especificada'),
                    ]),

                Section::make('🔍 Estado de Inspección')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Infolists\Components\IconEntry::make('inspeccionado')
                                    ->label('Estado de Inspección')
                                    ->boolean()
                                    ->trueIcon('heroicon-o-check-circle')
                                    ->falseIcon('heroicon-o-clock')
                                    ->trueColor('success')
                                    ->falseColor('warning'),

                                Infolists\Components\TextEntry::make('fecha_inspeccion')
                                    ->label('Fecha de Inspección')
                                    ->dateTime('d/m/Y H:i')
                                    ->placeholder('No inspeccionado')
                                    ->icon('heroicon-o-calendar'),
                            ]),

                        Infolists\Components\TextEntry::make('inspector.name')
                            ->label('👤 Inspector')
                            ->placeholder('No asignado'),

                        Infolists\Components\TextEntry::make('observaciones_campo')
                            ->label('📝 Observaciones')
                            ->placeholder('Sin observaciones')
                            ->columnSpanFull(),
                    ]),

                Section::make('🛣️ Asignación de Ruta')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('ruta.nombre')
                                    ->label('Ruta Asignada')
                                    ->placeholder('Sin asignar')
                                    ->icon('heroicon-o-map'),

                                Infolists\Components\TextEntry::make('orden_en_ruta')
                                    ->label('Orden en Ruta')
                                    ->badge()
                                    ->color('info')
                                    ->icon('heroicon-o-numbered-list'),
                            ]),
                    ]),
            ]);
    }
}
