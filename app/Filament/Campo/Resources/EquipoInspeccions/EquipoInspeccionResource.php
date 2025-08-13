<?php

namespace App\Filament\Campo\Resources\EquipoInspeccions;

use App\Filament\Campo\Resources\EquipoInspeccions\Pages\CreateEquipoInspeccion;
use App\Filament\Campo\Resources\EquipoInspeccions\Pages\EditEquipoInspeccion;
use App\Filament\Campo\Resources\EquipoInspeccions\Pages\ListEquipoInspeccions;
use App\Filament\Campo\Resources\EquipoInspeccions\Pages\ViewEquipoInspeccion;
use App\Filament\Campo\Resources\EquipoInspeccions\Schemas\EquipoInspeccionForm;
use App\Filament\Campo\Resources\EquipoInspeccions\Schemas\EquipoInspeccionInfolist;
use App\Filament\Campo\Resources\EquipoInspeccions\Tables\EquipoInspeccionsTable;
use App\Models\EquipoInspeccion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EquipoInspeccionResource extends Resource
{
    protected static ?string $model = EquipoInspeccion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'nombre';

    public static function form(Schema $schema): Schema
    {
        return EquipoInspeccionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipoInspeccionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipoInspeccionsTable::configure($table);
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
            'index' => ListEquipoInspeccions::route('/'),
            'create' => CreateEquipoInspeccion::route('/create'),
            'view' => ViewEquipoInspeccion::route('/{record}'),
            'edit' => EditEquipoInspeccion::route('/{record}/edit'),
        ];
    }
}
