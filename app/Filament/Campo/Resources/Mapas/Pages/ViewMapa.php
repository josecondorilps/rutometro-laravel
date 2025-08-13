<?php

namespace App\Filament\Campo\Resources\Mapas\Pages;

use App\Filament\Campo\Resources\Mapas\MapaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMapa extends ViewRecord
{
    protected static string $resource = MapaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
