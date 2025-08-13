<?php

namespace App\Filament\Campo\Resources\EquipoInspeccions\Pages;

use App\Filament\Campo\Resources\EquipoInspeccions\EquipoInspeccionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipoInspeccion extends EditRecord
{
    protected static string $resource = EquipoInspeccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
