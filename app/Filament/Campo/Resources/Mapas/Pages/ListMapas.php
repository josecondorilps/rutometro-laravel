<?php

namespace App\Filament\Campo\Resources\Mapas\Pages;

use App\Filament\Campo\Resources\Mapas\MapaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMapas extends ListRecords
{
    protected static string $resource = MapaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
