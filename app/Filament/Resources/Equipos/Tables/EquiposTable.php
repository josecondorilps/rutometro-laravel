<?php

namespace App\Filament\Resources\Equipos\Tables;

use App\Models\Equipo;
use App\Services\CsvImportService; // ← AGREGAR IMPORT
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Exception; // ← AGREGAR IMPORT

class EquiposTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                // 🔥 BOTÓN CARGAR CSV (usando Service)
                Action::make('importar_csv')
                    ->label('Cargar CSV')
                    ->icon('heroicon-o-document-arrow-up')
                    ->color('success')
                    ->form([
                        FileUpload::make('csv_file')
                            ->label('Archivo CSV')
                            ->acceptedFileTypes(['text/csv', '.csv', 'text/plain'])
                            ->required()
                            ->directory('csvs')
                            ->preserveFilenames()
                            ->disk('public')
                            ->maxSize(10240) // 10MB máximo
                            ->helperText('Formato: Identification, Lat, Long, Address, Type, Area, Usage state, Remarks'),
                    ])
                    ->action(function (array $data) {
                        $service = new CsvImportService();

                        try {
                            $stats = $service->processCsv(
                                $data['csv_file'],
                                auth()->id()
                            );

                            Notification::make()
                                ->title('CSV importado exitosamente')
                                ->body(self::formatStatsMessage($stats))
                                ->success()
                                ->duration(8000)
                                ->send();

                        } catch (Exception $e) {
                            Notification::make()
                                ->title('Error al procesar CSV')
                                ->body($e->getMessage())
                                ->danger()
                                ->duration(10000)
                                ->send();
                        }
                    }),

                // 🗺️ BOTÓN VER MAPA
                Action::make('ver_mapa')
                    ->label('Ver en Mapa')
                    ->icon('heroicon-o-map')
                    ->color('primary')
                    ->url(fn () => '#') // Cambiar por ruta del mapa
                    ->openUrlInNewTab(),

                // 📊 BOTÓN HISTORIAL IMPORTACIONES
                Action::make('historial_csv')
                    ->label('Historial CSV')
                    ->icon('heroicon-o-document-text')
                    ->color('gray')
                    ->url(fn () => '/admin/csv-imports') // Si creas el resource
                    ->openUrlInNewTab(),
            ])
            ->columns([
                TextColumn::make('identificador')
                    ->label('ID Equipo')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Transformador' => 'warning',
                        'Poste' => 'info',
                        'Medidor' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('latitud')
                    ->label('Latitud')
                    ->numeric(decimalPlaces: 6),

                TextColumn::make('longitud')
                    ->label('Longitud')
                    ->numeric(decimalPlaces: 6),

                TextColumn::make('direccion')
                    ->label('Dirección')
                    ->limit(40)
                    ->tooltip(function ($record) {
                        return $record->direccion;
                    }),

                TextColumn::make('area')
                    ->label('Área')
                    ->badge(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'operativo' => 'success',
                        'mantenimiento' => 'warning',
                        'dañado' => 'danger',
                        default => 'gray',
                    }),

                IconColumn::make('inspeccionado')
                    ->label('Inspeccionado')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('ruta.nombre')
                    ->label('Ruta Asignada')
                    ->placeholder('Sin asignar')
                    ->color('warning'),

                TextColumn::make('created_at')
                    ->label('Importado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo de Equipo'),

                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'operativo' => 'Operativo',
                        'mantenimiento' => 'Mantenimiento',
                        'dañado' => 'Dañado',
                    ]),

                TernaryFilter::make('inspeccionado')
                    ->label('Inspeccionado'),

                SelectFilter::make('area')
                    ->label('Área'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),

                // Acción: Marcar como inspeccionado
                Action::make('inspeccionar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Equipo $record) {
                        $record->update([
                            'inspeccionado' => true,
                            'fecha_inspeccion' => now(),
                            'inspeccionado_por' => auth()->id()
                        ]);

                        Notification::make()
                            ->title('Equipo marcado como inspeccionado')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Equipo $record) => !$record->inspeccionado),
            ])
            ->bulkActions([
                \Filament\Actions\DeleteBulkAction::make(),

                // Acción masiva: Marcar como inspeccionado
                Action::make('marcar_inspeccionados')
                    ->label('Marcar como Inspeccionados')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        $count = 0;
                        foreach ($records as $record) {
                            if (!$record->inspeccionado) {
                                $record->update([
                                    'inspeccionado' => true,
                                    'fecha_inspeccion' => now(),
                                    'inspeccionado_por' => auth()->id()
                                ]);
                                $count++;
                            }
                        }

                        Notification::make()
                            ->title("Equipos marcados como inspeccionados: {$count}")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    // MÉTODO PARA FORMATEAR MENSAJE DE ESTADÍSTICAS
    protected static function formatStatsMessage(array $stats): string
    {
        $message = "📊 Resultados de importación:\n";
        $message .= "• Total filas: {$stats['total_rows']}\n";
        $message .= "• Procesados: {$stats['processed']}\n";
        $message .= "• Creados: {$stats['created']}\n";
        $message .= "• Actualizados: {$stats['updated']}\n";

        if ($stats['skipped'] > 0) {
            $message .= "• Omitidos: {$stats['skipped']}\n";
        }

        if ($stats['errors'] > 0) {
            $message .= "• Errores: {$stats['errors']}";
        }

        return $message;
    }
}
