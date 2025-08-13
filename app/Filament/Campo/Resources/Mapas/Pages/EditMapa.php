<?php

namespace App\Filament\Campo\Resources\Mapas\Pages;

use App\Filament\Campo\Resources\Mapas\MapaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMapa extends EditRecord
{
    protected static string $resource = MapaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
