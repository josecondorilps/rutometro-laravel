<div class="space-y-4">
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-semibold text-lg mb-2">📊 Estadísticas de la Ruta {{ $rutaId }}</h4>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-3 rounded border">
                <div class="text-2xl font-bold text-blue-600">{{ $stats['total_equipos'] }}</div>
                <div class="text-sm text-gray-600">Total Equipos</div>
            </div>

            <div class="bg-white p-3 rounded border">
                <div class="text-2xl font-bold text-green-600">{{ $stats['equipos_activos'] }}</div>
                <div class="text-sm text-gray-600">Equipos Activos</div>
            </div>

            <div class="bg-white p-3 rounded border">
                <div class="text-2xl font-bold text-orange-600">{{ $stats['equipos_inspeccionados'] }}</div>
                <div class="text-sm text-gray-600">Inspeccionados</div>
            </div>

            <div class="bg-white p-3 rounded border">
                @php
                    $progreso = $stats['total_equipos'] > 0
                        ? round(($stats['equipos_inspeccionados'] / $stats['total_equipos']) * 100, 1)
                        : 0;
                @endphp
                <div class="text-2xl font-bold text-purple-600">{{ $progreso }}%</div>
                <div class="text-sm text-gray-600">Progreso</div>
            </div>
        </div>
    </div>

    <div class="bg-blue-50 p-4 rounded-lg">
        <h5 class="font-medium mb-2">ℹ️ Información</h5>
        <ul class="space-y-1 text-sm">
            <li>• Los equipos se muestran como puntos en el mapa</li>
            <li>• Cada color representa un tipo diferente de equipo</li>
            <li>• Haz clic en un punto para ver detalles del equipo</li>
            <li>• Los equipos se ordenan según el orden de ruta</li>
        </ul>
    </div>

    @if($stats['total_equipos'] === 0)
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
            <div class="flex items-center">
                <div class="text-yellow-600 mr-2">⚠️</div>
                <div>
                    <div class="font-medium">Sin equipos en esta ruta</div>
                    <div class="text-sm text-gray-600">No se encontraron equipos asociados a la ruta {{ $rutaId }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
