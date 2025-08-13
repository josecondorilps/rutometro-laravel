<?php

namespace App\Filament\Campo\Resources\MisRutas;

use App\Filament\Campo\Resources\MisRutas\Pages\CreateMisRutas;
use App\Filament\Campo\Resources\MisRutas\Pages\EditMisRutas;
use App\Filament\Campo\Resources\MisRutas\Pages\ListMisRutas;
use App\Filament\Campo\Resources\MisRutas\Pages\ViewMisRutas;
use App\Filament\Campo\Resources\MisRutas\Schemas\MisRutasForm;
use App\Filament\Campo\Resources\MisRutas\Schemas\MisRutasInfolist;
use App\Filament\Campo\Resources\MisRutas\Tables\MisRutasTable;
use App\Models\MisRutas;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MisRutasResource extends Resource
{
    protected static ?string $model = MisRutas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'misrutas';

    public static function form(Schema $schema): Schema
    {
        return MisRutasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MisRutasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MisRutasTable::configure($table);
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
            'index' => ListMisRutas::route('/'),
            'create' => CreateMisRutas::route('/create'),
            'view' => ViewMisRutas::route('/{record}'),
            'edit' => EditMisRutas::route('/{record}/edit'),
        ];
    }
}
