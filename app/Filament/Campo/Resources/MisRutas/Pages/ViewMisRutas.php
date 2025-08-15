<?php

namespace App\Filament\Campo\Resources\MisRutas\Pages;

use App\Filament\Campo\Resources\MisRutas\MisRutasResource;
use App\Models\MisRutas;
use App\Models\Ruta;
use App\Models\Equipo;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMisRutas extends ViewRecord
{
    protected static string $resource = MisRutasResource::class;
    protected string $view = 'filament.campo.resources.mis-rutas.pages.mapa-interactivo';
    protected static ?string $title = 'Mapa Interactivo - Equipos de la Ruta';
    protected static ?string $navigationLabel = 'Mapa de Equipos';

    protected function getViewData(): array
    {
        $misRuta = $this->getRecord();

        // Obtener el ID de la ruta desde la URL o desde el registro
        $rutaId = $misRuta->ruta_id;

        \Log::info('Cargando equipos por ruta ID desde URL:', [
            'ruta_id' => $rutaId,
            'mis_ruta_id' => $misRuta->id,
            'user_id' => auth()->id()
        ]);

        // Información de la asignación personal
        $misRutaInfo = [
            'id' => $misRuta->id,
            'nombre_personalizado' => $misRuta->nombre,
            'estado_asignacion' => $misRuta->estado ?? 'asignado',
            'fecha_asignacion' => $misRuta->fecha_asignacion?->format('Y-m-d H:i:s'),
            'user_id' => $misRuta->user_id,
            'ruta_id' => $rutaId,
        ];

        // Obtener la ruta directamente por ID
        $ruta = Ruta::find($rutaId);

        // Información de la ruta
        $rutaInfo = [
            'id' => $rutaId,
            'nombre' => $ruta?->nombre ?? 'Ruta no encontrada',
            'descripcion' => $ruta?->descripcion,
            'total_equipos' => 0,
            'equipos_inspeccionados' => 0,
            'equipos_pendientes' => 0,
            'tipos_equipos' => [],
            'areas_cubiertas' => [],
        ];

        // Obtener equipos directamente por ruta_id
        $equipos = collect();

        try {
            if (!$ruta) {
                throw new \Exception("Ruta con ID {$rutaId} no encontrada");
            }

            // CONSULTA DIRECTA: Obtener todos los equipos de esta ruta
            $equiposRaw = Equipo::where('ruta_id', $rutaId)
                ->select([
                    'id',
                    'identificador',
                    'latitud',
                    'longitud',
                    'altitud',
                    'tipo',
                    'direccion',
                    'area',
                    'estado',
                    'panorama_filename',
                    'panorama_thumbnail',
                    'panorama_description',
                    'ruta_id',
                    'orden_en_ruta',
                    'qr_code_path',
                    'inspeccionado',
                    'fecha_inspeccion',
                    'inspeccionado_por',
                    'observaciones_campo',
                    'created_at',
                    'updated_at'
                ])
                ->orderBy('orden_en_ruta', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            \Log::info('Equipos obtenidos directamente por ruta_id:', [
                'ruta_id' => $rutaId,
                'total_equipos' => $equiposRaw->count(),
                'equipos_ids' => $equiposRaw->pluck('id')->toArray()
            ]);

            if ($equiposRaw->isEmpty()) {
                \Log::warning('No se encontraron equipos para esta ruta', [
                    'ruta_id' => $rutaId
                ]);
            }

            // Procesar cada equipo usando el método del modelo
            $equipos = $equiposRaw->map(function ($equipo, $index) use ($ruta) {
                // Usar el método toPanoramaArray() del modelo
                $equipoArray = $equipo->toPanoramaArray();

                // Agregar información adicional específica
                return array_merge($equipoArray, [
                    // Información de ubicación y orden
                    'altitud' => (float) ($equipo->altitud ?? 0),
                    'area' => $equipo->area ?? 'Área no especificada',
                    'orden_en_ruta' => $equipo->orden_en_ruta ?? ($index + 1),
                    'direccion' => $equipo->direccion,

                    // Estado de inspección
                    'inspeccionado' => $equipo->inspeccionado,
                    'fecha_inspeccion' => $equipo->fecha_inspeccion?->format('Y-m-d H:i:s'),
                    'inspector_id' => $equipo->inspeccionado_por,
                    'observaciones' => $equipo->observaciones_campo,

                    // Archivos y recursos
                    'qr_code_path' => $equipo->qr_code_path,
                    'panorama_description' => $equipo->panorama_description,

                    // Información para el popup
                    'popup_title' => "📡 {$equipo->identificador}",
                    'popup_subtitle' => $equipo->tipo ? ucfirst($equipo->tipo) : 'Equipo',
                    'popup_content' => $this->buildEquipoPopupContent($equipo),

                    // Metadatos
                    'created_at' => $equipo->created_at?->format('Y-m-d H:i:s'),
                    'updated_at' => $equipo->updated_at?->format('Y-m-d H:i:s'),

                    // Contexto de ruta
                    'ruta_nombre_completo' => $ruta->nombre,
                ]);
            });

            // Calcular estadísticas automáticamente
            $rutaInfo['total_equipos'] = $equipos->count();
            $rutaInfo['equipos_inspeccionados'] = $equipos->where('inspeccionado', true)->count();
            $rutaInfo['equipos_pendientes'] = $equipos->where('inspeccionado', false)->count();
            $rutaInfo['tipos_equipos'] = $equipos->pluck('tipo')->filter()->unique()->values()->toArray();
            $rutaInfo['areas_cubiertas'] = $equipos->pluck('area')->filter()->unique()->values()->toArray();
            $rutaInfo['progreso_inspeccion'] = $rutaInfo['total_equipos'] > 0
                ? round(($rutaInfo['equipos_inspeccionados'] / $rutaInfo['total_equipos']) * 100, 1)
                : 0;

            \Log::info('Procesamiento completado exitosamente:', [
                'ruta_id' => $rutaId,
                'total_equipos' => $rutaInfo['total_equipos'],
                'inspeccionados' => $rutaInfo['equipos_inspeccionados'],
                'pendientes' => $rutaInfo['equipos_pendientes'],
                'progreso' => $rutaInfo['progreso_inspeccion'] . '%',
                'tipos' => $rutaInfo['tipos_equipos'],
                'areas' => $rutaInfo['areas_cubiertas']
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener equipos por ruta_id:', [
                'ruta_id' => $rutaId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Crear mensaje de error específico
            $equipos = collect([
                [
                    'id' => 'error_ruta_' . $rutaId,
                    'identificador' => 'ERROR_RUTA',
                    'latitude' => -12.0464,
                    'longitude' => -77.0428,
                    'filename' => 'error_ruta.jpg',
                    'address' => 'Error al cargar equipos de la ruta',
                    'thumbnail' => 'https://via.placeholder.com/200x120/F44336/white?text=ERROR+RUTA',
                    'tipo' => 'error',
                    'estado' => 'error',
                    'area' => 'Error',
                    'inspeccionado' => false,
                    'popup_title' => '❌ Error de Carga',
                    'popup_subtitle' => 'Ruta ID: ' . $rutaId,
                    'popup_content' => $e->getMessage()
                ]
            ]);
        }

        return [
            'misRuta' => $misRutaInfo,
            'rutaInfo' => $rutaInfo,
            'equipos' => $equipos,  // ← EQUIPOS DE LA RUTA ESPECÍFICA
            'debug' => [
                'ruta_id_from_url' => $rutaId,
                'equipos_count' => $equipos->count(),
                'query_method' => 'direct_by_ruta_id',
                'user_id' => auth()->id(),
                'timestamp' => now()->toISOString(),
            ]
        ];
    }

    /**
     * Construir contenido del popup para cada equipo
     */
    private function buildEquipoPopupContent($equipo): string
    {
        $content = [];

        $content[] = "🆔 <strong>ID:</strong> {$equipo->identificador}";

        if ($equipo->tipo) {
            $content[] = "🔧 <strong>Tipo:</strong> " . ucfirst($equipo->tipo);
        }

        if ($equipo->estado) {
            $estadoIcon = $equipo->estado === 'activo' ? '✅' : ($equipo->estado === 'inactivo' ? '❌' : '⚠️');
            $content[] = "{$estadoIcon} <strong>Estado:</strong> " . ucfirst($equipo->estado);
        }

        if ($equipo->area) {
            $content[] = "📍 <strong>Área:</strong> {$equipo->area}";
        }

        if ($equipo->orden_en_ruta) {
            $content[] = "🔢 <strong>Orden:</strong> {$equipo->orden_en_ruta}";
        }

        $content[] = $equipo->inspeccionado
            ? "✅ <strong>Inspeccionado:</strong> " . ($equipo->fecha_inspeccion ?? 'Sí')
            : "⏳ <strong>Estado:</strong> Pendiente de inspección";

        if ($equipo->observaciones_campo) {
            $content[] = "📝 <strong>Observaciones:</strong> {$equipo->observaciones_campo}";
        }

        return implode('<br>', $content);
    }

    /**
     * Obtener estadísticas rápidas de la ruta
     */
    public function getRutaStats()
    {
        $misRuta = $this->getRecord();
        $rutaId = $misRuta->ruta_id;

        return [
            'total_equipos' => Equipo::where('ruta_id', $rutaId)->count(),
            'equipos_activos' => Equipo::where('ruta_id', $rutaId)->where('estado', 'activo')->count(),
            'equipos_inspeccionados' => Equipo::where('ruta_id', $rutaId)->where('inspeccionado', true)->count(),
        ];
    }

    protected function getHeaderActions(): array
    {
        $rutaId = $this->getRecord()->ruta_id;
        $equiposCount = $this->getRutaStats()['total_equipos'];

        return [
            CreateAction::make('refresh_equipos')
                ->label('Recargar Equipos')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    // Recargar la página
                    return redirect()->refresh();
                }),

            CreateAction::make('equipos_info')
                ->label("Ver Info ({$equiposCount} equipos)")
                ->icon('heroicon-o-information-circle')
                ->modalHeading('Información de Equipos de la Ruta')
                ->modalContent(view('filament.campo.modals.equipos-info', [
                    'rutaId' => $rutaId,
                    'equiposCount' => $equiposCount,
                    'stats' => $this->getRutaStats()
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar'),

            CreateAction::make('export_equipos')
                ->label('Exportar Equipos')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () use ($rutaId) {
                    return $this->exportEquiposRuta($rutaId);
                }),

            CreateAction::make('generar_reporte')
                ->label('Generar Reporte')
                ->icon('heroicon-o-document-text')
                ->action(function () use ($rutaId) {
                    return $this->generarReporteRuta($rutaId);
                }),
        ];
    }

    /**
     * Exportar equipos de la ruta
     */
    private function exportEquiposRuta($rutaId)
    {
        try {
            $equipos = Equipo::where('ruta_id', $rutaId)->get();

            if ($equipos->isEmpty()) {
                $this->notify('warning', "No hay equipos para exportar en la ruta {$rutaId}");
                return;
            }

            // Aquí puedes implementar la lógica de exportación real
            // Por ejemplo, generar CSV, Excel, etc.

            $this->notify('success', "Preparando exportación de {$equipos->count()} equipos de la ruta {$rutaId}");

            // Opcional: Redirigir a descarga
            // return response()->download($archivoGenerado);

        } catch (\Exception $e) {
            \Log::error('Error exportando equipos:', [
                'ruta_id' => $rutaId,
                'error' => $e->getMessage()
            ]);

            $this->notify('danger', 'Error al exportar equipos: ' . $e->getMessage());
        }
    }

    /**
     * Generar reporte de la ruta
     */
    private function generarReporteRuta($rutaId)
    {
        try {
            $ruta = Ruta::find($rutaId);
            $equipos = Equipo::where('ruta_id', $rutaId)->get();
            $stats = $this->getRutaStats();

            // Generar reporte en PDF o HTML
            $reporteData = [
                'ruta' => $ruta,
                'equipos' => $equipos,
                'estadisticas' => $stats,
                'fecha_generacion' => now()->format('Y-m-d H:i:s'),
                'generado_por' => auth()->user()->name
            ];

            $this->notify('success', "Reporte generado para la ruta: {$ruta->nombre}");

            // Aquí puedes implementar la generación real del PDF
            // return PDF::loadView('reports.ruta-equipos', $reporteData)->download();

        } catch (\Exception $e) {
            \Log::error('Error generando reporte:', [
                'ruta_id' => $rutaId,
                'error' => $e->getMessage()
            ]);

            $this->notify('danger', 'Error al generar reporte: ' . $e->getMessage());
        }
    }
}
