<?php
// Modifica ListMisRutas.php
namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource;
use App\Filament\Campo\Resources\MisRutas\Tables\MisRutasTable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Table;
use App\Models\Ruta;
use App\Models\MisRutas;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

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
