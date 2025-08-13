<?php

namespace App\Filament\Campo\Resources\EquipoInspeccions\Pages;

use App\Filament\Campo\Resources\EquipoInspeccions\EquipoInspeccionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEquipoInspeccion extends ViewRecord
{
    protected static string $resource = EquipoInspeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
