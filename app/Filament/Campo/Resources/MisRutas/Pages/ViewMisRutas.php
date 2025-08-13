<?php

namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMisRutas extends ViewRecord
{
    protected static string $resource = MisRutasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
