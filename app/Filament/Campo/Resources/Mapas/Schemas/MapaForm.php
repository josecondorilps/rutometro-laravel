<?php

namespace App\Filament\Campo\Resources\Mapas\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;

class MapaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
               Section::make('Información del Equipo')
                    ->description('Datos básicos del equipo en campo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                               TextInput::make('identificador')
                                    ->label('Identificador del Equipo')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Ej: ANT-001, REP-025, TORRE-15')
                                    ->maxLength(50)
                                    ->prefixIcon('heroicon-o-identification'),

                              Select::make('tipo')
                                    ->label('Tipo de Equipo')
                                    ->options([
                                        'antena' => 'Antena',
                                        'repetidor' => 'Repetidor',
                                        'torre' => 'Torre',
                                        'gabinete' => 'Gabinete',
                                        'router' => 'Router',
                                        'switch' => 'Switch',
                                        'servidor' => 'Servidor',
                                        'ups' => 'UPS',
                                        'fibra_optica' => 'Fibra Óptica',
                                        'otro' => 'Otro',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->native(false),

                               Select::make('estado')
                                    ->label('Estado Operativo')
                                    ->options([
                                        'operativo' => 'Operativo',
                                        'mantenimiento' => 'En Mantenimiento',
                                        'fuera_servicio' => 'Fuera de Servicio',
                                        'pendiente' => 'Pendiente',
                                    ])
                                    ->default('operativo')
                                    ->required()
                                    ->native(false),

                               TextInput::make('area')
                                    ->label('Área/Zona de Cobertura')
                                    ->placeholder('Ej: Zona Norte, Sector A, Centro')
                                    ->maxLength(100)
                                    ->prefixIcon('heroicon-o-map-pin'),
                            ]),
                    ]),

                Section::make('Ubicación GPS')
                    ->description('Coordenadas geográficas exactas del equipo')
                    ->schema([
                       Grid::make(3)
                            ->schema([
                               TextInput::make('latitud')
                                    ->label('Latitud')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('19.4326')
                                    ->helperText('Grados decimales (Ej: 19.4326)')
                                    ->required()
                                    ->prefixIcon('heroicon-o-globe-alt'),

                                TextInput::make('longitud')
                                    ->label('Longitud')
                                    ->numeric()
                                    ->step(0.0000001)
                                    ->placeholder('-99.1332')
                                    ->helperText('Grados decimales (Ej: -99.1332)')
                                    ->required()
                                    ->prefixIcon('heroicon-o-globe-alt'),

                                TextInput::make('altitud')
                                    ->label('Altitud (m.s.n.m.)')
                                    ->numeric()
                                    ->step(0.1)
                                    ->placeholder('2240')
                                    ->helperText('Metros sobre el nivel del mar')
                                    ->prefixIcon('heroicon-o-arrow-trending-up'),
                            ]),

                        Textarea::make('direccion')
                            ->label('Dirección Completa')
                            ->placeholder('Dirección física del equipo para ubicación en campo')
                            ->rows(2)
                            ->columnSpanFull(),

                        // Vista previa de coordenadas
                        Placeholder::make('vista_previa_coordenadas')
                            ->label('Vista Previa GPS')
                            ->content(fn ($get) =>
                            $get('latitud') && $get('longitud') ?
                                new \Illuminate\Support\HtmlString("
                                    <div class='bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-center'>
                                        <p class='text-sm text-blue-600 dark:text-blue-400 mb-2'>
                                            📍 Coordenadas: {$get('latitud')}, {$get('longitud')}
                                        </p>
                                        <a href='https://www.google.com/maps?q={$get('latitud')},{$get('longitud')}'
                                           target='_blank'
                                           class='text-blue-500 hover:text-blue-700 text-sm'>
                                            🗺️ Ver en Google Maps
                                        </a>
                                    </div>
                                ") :
                                new \Illuminate\Support\HtmlString("
                                    <div class='bg-gray-50 dark:bg-gray-700 p-4 rounded-lg text-center'>
                                        <p class='text-sm text-gray-500 dark:text-gray-400'>
                                            Ingresa coordenadas para ver vista previa
                                        </p>
                                    </div>
                                ")
                            )
                            ->columnSpanFull()
                            ->visible(fn ($get) => $get('latitud') || $get('longitud')),
                    ]),

                Section::make('Asignación de Ruta')
                    ->description('Ruta de inspección y orden de visita')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('ruta_id')
                                    ->label('Ruta de Inspección')
                                    ->relationship('ruta', 'nombre')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('nombre')
                                            ->label('Nombre de la Ruta')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Ej: Ruta Norte LPS, Zona Centro'),

                                        Textarea::make('descripcion')
                                            ->label('Descripción')
                                            ->rows(3)
                                            ->placeholder('Descripción de la ruta de inspección'),
                                    ])
                                    ->prefixIcon('heroicon-o-map'),

                               TextInput::make('orden_en_ruta')
                                    ->label('Orden de Visita')
                                    ->numeric()
                                    ->minValue(1)
                                    ->step(1)
                                    ->placeholder('1, 2, 3...')
                                    ->helperText('Secuencia de inspección en la ruta')
                                    ->prefixIcon('heroicon-o-numbered-list'),
                            ]),
                    ]),

                Section::make('Estado de Inspección')
                    ->description('Control de inspecciones de campo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('inspeccionado')
                                    ->label('¿Equipo ya inspeccionado?')
                                    ->default(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $set('fecha_inspeccion', now());
                                            $set('inspeccionado_por', auth()->id());
                                        } else {
                                            $set('fecha_inspeccion', null);
                                            $set('inspeccionado_por', null);
                                        }
                                    }),

                                Select::make('inspeccionado_por')
                                    ->label('Inspector Asignado')
                                    ->relationship('inspector', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->visible(fn (callable $get) => $get('inspeccionado'))
                                    ->prefixIcon('heroicon-o-user'),
                            ]),

                       DateTimePicker::make('fecha_inspeccion')
                            ->label('Fecha y Hora de Inspección')
                            ->visible(fn (callable $get) => $get('inspeccionado'))
                            ->default(now())
                            ->prefixIcon('heroicon-o-calendar'),

                        Textarea::make('observaciones_campo')
                            ->label('Observaciones de Campo')
                            ->placeholder('Notas técnicas, incidencias, recomendaciones de mantenimiento...')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),

                    Section::make('Identificación QR')
                    ->description('Código QR para identificación rápida en campo')
                    ->schema([
                        FileUpload::make('qr_code_path')
                            ->label('Código QR del Equipo')
                            ->image()
                            ->directory('qr-codes-equipos')
                            ->visibility('private')
                            ->acceptedFileTypes(['image/png', 'image/jpeg'])
                            ->maxSize(2048)
                            ->helperText('Imagen del código QR para escaneo en campo'),
                    ]),
            ]);
    }
}
