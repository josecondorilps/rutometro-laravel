<?php

namespace App\Filament\Campo\Resources\MisRutas\Tables;

use App\Models\Ruta;
use App\Models\MisRutas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;

use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class MisRutasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ruta.total_equipos')
                    ->label('Equipos')
                    ->sortable()
                    ->default('N/A'),
                TextColumn::make('ruta.distancia_km')
                    ->label('Distancia (km)')
                    ->sortable()
                    ->default('N/A'),
                TextColumn::make('ruta.tiempo_estimado_minutos')
                    ->label('Tiempo (min)')
                    ->sortable()
                    ->default('N/A'),
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'asignado' => 'success',
                        'en_progreso' => 'warning',
                        'completado' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('fecha_asignacion')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                Action::make('desasignar')
                    ->label('Desasignar')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Desasignar ruta?')
                    ->modalDescription('Esta acción liberará la ruta para que otros usuarios puedan tomarla.')
                    ->action(function (MisRutas $record) {
                        // Liberar la ruta en la tabla rutas
                        $record->ruta->update([
                            'asignado_a' => null,
                            'estado' => 'activo', // o el estado que uses para disponible
                            'fecha_asignacion' => null,
                        ]);

                        // Eliminar de MisRutas
                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Ruta liberada')
                            ->body('La ruta está ahora disponible para otros usuarios.')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('agregarRuta')
                    ->label('Rutas disponibles')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->modalHeading('Agregar Ruta a Mis Rutas')
                    ->modalDescription('Selecciona una ruta para agregar a tu lista personal')
                    ->modalWidth('5xl')
                    ->form([
                        Select::make('ruta_id')
                            ->label('Seleccionar Ruta')
                            ->searchable()
                            ->placeholder('Busca una ruta disponible...')
                            ->getSearchResultsUsing(fn (string $search): array =>
                            Ruta::where('nombre', 'like', "%{$search}%")
                                ->where(function ($query) {
                                    $query->whereNull('asignado_a')
                                        ->orWhere('estado', '!=', 'asignado');
                                })
                                ->limit(50)
                                ->pluck('nombre', 'id')
                                ->toArray()
                            )
                            ->getOptionLabelUsing(fn ($value): ?string =>
                            Ruta::find($value)?->nombre
                            )
                            ->options(
                                Ruta::where(function ($query) {
                                    $query->whereNull('asignado_a')
                                        ->orWhere('estado', '!=', 'asignado');
                                })
                                    ->limit(20)
                                    ->pluck('nombre', 'id')
                            )
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $ruta = Ruta::find($state);
                                    if ($ruta) {
                                        $set('preview_equipos', $ruta->total_equipos ?? 'N/A');
                                        $set('preview_distancia', $ruta->distancia_km ?? 'N/A');
                                        $set('preview_tiempo', $ruta->tiempo_estimado_minutos ?? 'N/A');
                                    }
                                }
                            }),
                        // Preview de la ruta seleccionada
                        Grid::make(3)
                            ->schema([
                                TextInput::make('preview_equipos')
                                    ->label('Total Equipos')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('preview_distancia')
                                    ->label('Distancia (km)')
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('preview_tiempo')
                                    ->label('Tiempo Estimado (min)')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->visible(fn ($get) => $get('ruta_id')),
                    ])
                    ->action(function (array $data) {
                        $rutaId = $data['ruta_id'];
                        $ruta = Ruta::find($rutaId);

                        if (!$ruta) {
                            \Filament\Notifications\Notification::make()
                                ->title('Error')
                                ->body('La ruta seleccionada no existe.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // ✅ Verificar si la ruta ya está asignada a alguien
                        if ($ruta->asignado_a && $ruta->estado === 'asignado') {
                            \Filament\Notifications\Notification::make()
                                ->title('Ruta no disponible')
                                ->body('Esta ruta ya está asignada a otro usuario.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // Verificar duplicados en MisRutas
                        $existe = MisRutas::where('user_id', auth()->id())
                            ->where('ruta_id', $rutaId)
                            ->exists();

                        if ($existe) {
                            \Filament\Notifications\Notification::make()
                                ->title('Ruta duplicada')
                                ->body('Esta ruta ya está en tu lista.')
                                ->warning()
                                ->send();
                            return;
                        }

                        // ✅ Actualizar la tabla rutas (UPDATE)
                        $ruta->update([
                            'asignado_a' => auth()->id(),
                            'estado' => 'asignado',
                            'fecha_asignacion' => now(),
                        ]);

                        // ✅ Crear entrada en MisRutas (INSERT)
                        MisRutas::create([
                            'user_id' => auth()->id(),
                            'ruta_id' => $rutaId,
                            'nombre' => $ruta->nombre,
                            'estado' => 'asignado',
                            'fecha_asignacion' => now(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('¡Ruta asignada!')
                            ->body("'{$ruta->nombre}' se asignó correctamente.")
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
