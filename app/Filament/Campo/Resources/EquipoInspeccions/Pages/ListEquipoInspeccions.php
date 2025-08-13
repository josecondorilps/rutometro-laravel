<?php

namespace App\Filament\Campo\Resources\EquipoInspeccions\Pages;

use App\Filament\Campo\Resources\EquipoInspeccions\EquipoInspeccionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipoInspeccions extends ListRecords
{
    protected static string $resource = EquipoInspeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
