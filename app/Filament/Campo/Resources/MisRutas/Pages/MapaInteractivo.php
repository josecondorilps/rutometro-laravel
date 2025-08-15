<?php

namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource; // ← Mantener esta línea (era correcta)
use App\Models\MisRutas;
use Filament\Resources\Pages\Page;

class MapaInteractivo extends Page
{
    protected static string $resource = MisRutasResource::class;

    protected string $view = 'filament.campo.resources.mis-rutas.pages.mapa-interactivo';

    protected static ?string $title = 'Mapa Interactivo de Mis Rutas';

    protected static ?string $navigationLabel = 'Mapa Interactivo';


    // Pasar datos a la vista
    protected function getViewData(): array
    {
        return [
            'misRutas' => MisRutas::all(),
        ];
    }
}
