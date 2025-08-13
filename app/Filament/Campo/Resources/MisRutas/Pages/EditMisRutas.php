<?php

namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMisRutas extends EditRecord
{
    protected static string $resource = MisRutasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
