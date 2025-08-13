<?php

namespace App\Filament\Resources\Rutas;

use App\Filament\Resources\Rutas\Pages\CreateRuta;
use App\Filament\Resources\Rutas\Pages\EditRuta;
use App\Filament\Resources\Rutas\Pages\ListRutas;
use App\Filament\Resources\Rutas\Pages\ViewRuta;
use App\Filament\Resources\Rutas\Schemas\RutaForm;
use App\Filament\Resources\Rutas\Schemas\RutaInfolist;
use App\Filament\Resources\Rutas\Tables\RutasTable;
use App\Models\Ruta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RutaResource extends Resource
{
    protected static ?string $model = Ruta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return RutaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RutaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RutasTable::configure($table);
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
            'index' => ListRutas::route('/'),
            'create' => CreateRuta::route('/create'),
            'view' => ViewRuta::route('/{record}'),
            'edit' => EditRuta::route('/{record}/edit'),
        ];
    }
}
