<?php

namespace App\Filament\Campo\Resources\Mapas;

use App\Filament\Campo\Resources\Mapas\Pages\CreateMapa;
use App\Filament\Campo\Resources\Mapas\Pages\EditMapa;
use App\Filament\Campo\Resources\Mapas\Pages\ListMapas;
use App\Filament\Campo\Resources\Mapas\Pages\ViewMapa;
use App\Filament\Campo\Resources\Mapas\Schemas\MapaForm;
use App\Filament\Campo\Resources\Mapas\Schemas\MapaInfolist;
use App\Filament\Campo\Resources\Mapas\Tables\MapasTable;
use App\Models\Equipo;
use App\Models\Mapa;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MapaResource extends Resource
{
    protected static ?string $model = Equipo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static ?string $recordTitleAttribute = 'mapa';

    public static function form(Schema $schema): Schema
    {
        return MapaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MapaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MapasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMapas::route('/'),
            'create' => CreateMapa::route('/create'),
            'view' => ViewMapa::route('/{record}'),
            'edit' => EditMapa::route('/{record}/edit'),
        ];
    }
}
