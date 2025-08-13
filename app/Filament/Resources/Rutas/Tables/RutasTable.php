<?php

namespace App\Filament\Resources\Rutas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RutasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->searchable(),
                TextColumn::make('proyecto_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_equipos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('distancia_km')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tiempo_estimado_minutos')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('centro_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('centro_lng')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('estado'),
                TextColumn::make('asignado_a')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('fecha_asignacion')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
