<?php

namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMisRutas extends ListRecords
{
    protected static string $resource = MisRutasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
