{{-- resources/views/filament/campo/resources/mis-rutas/pages/mapa-interactivo.blade.php --}}
<x-filament-panels::page>
    @push('styles')
        <!-- Leaflet CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
              crossorigin="" />
        <!-- CSS personalizado si existe -->
        @if(file_exists(public_path('css/styles.css')))
            <link rel="stylesheet" href="{{ asset('css/styles.css') }}?v={{ filemtime(public_path('css/styles.css')) }}">
        @endif
        <style>
            .app-container { margin: 0; padding: 0; min-height: 600px; }
            .app-header {
                border-radius: 0.75rem;
                margin-bottom: 1rem;
                padding: 1rem;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
            }
            .main-content {
                border-radius: 0.75rem;
                overflow: hidden;
                min-height: 600px;
                border: 1px solid #e2e8f0;
            }
            @media (max-width: 768px) {
                .controls { flex-direction: column; gap: 0.5rem; }
                .sidebar { width: 100%; border-right: none; }
                .flex.h-\[600px\] { flex-direction: column; height: auto; }
                #map { height: 400px; }
            }
            .spinner {
                border: 3px solid #f3f3f3;
                border-top: 3px solid #3498db;
                border-radius: 50%;
                width: 2rem;
                height: 2rem;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            .info-badge {
                background: #e0f2fe;
                color: #0277bd;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                margin-right: 8px;
            }
        </style>
    @endpush

    <div class="app-container">
        <!-- Header con información de la ruta -->
        <header class="app-header">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <!-- Información de la ruta -->
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 mb-2">
                        🗺️ {{ $rutaInfo['nombre'] ?? 'Ruta sin nombre' }}
                    </h2>
                    <div class="flex flex-wrap gap-2">
                        @if(isset($rutaInfo['total_equipos']) && $rutaInfo['total_equipos'] > 0)
                            <span class="info-badge">📍 {{ $rutaInfo['total_equipos'] }} equipos</span>
                        @endif
                        @if(isset($rutaInfo['equipos_inspeccionados']))
                            <span class="info-badge">✅ {{ $rutaInfo['equipos_inspeccionados'] }} inspeccionados</span>
                        @endif
                        @if(isset($rutaInfo['progreso_inspeccion']))
                            <span class="info-badge">📊 {{ $rutaInfo['progreso_inspeccion'] }}% progreso</span>
                        @endif
                    </div>
                </div>

                <!-- Controles -->
                <div class="controls flex flex-wrap gap-2">
                    <button id="load-panoramas" class="fi-btn fi-btn--size-md fi-color-primary" type="button">
                        📍 Cargar Equipos
                    </button>
                    <button id="clear-map" class="fi-btn fi-btn--size-md fi-color-gray" type="button">
                        🧹 Limpiar
                    </button>
                    <button id="fit-map" class="fi-btn fi-btn--size-md fi-color-secondary" type="button">
                        🎯 Ajustar Vista
                    </button>
                </div>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="main-content">
            <div class="flex flex-col lg:flex-row h-auto lg:h-[600px]">
                <!-- Mapa -->
                <section class="map-container flex-1 relative">
                    <!-- El mapa -->
                    <div id="map" class="w-full h-full min-h-[400px]"></div>

                    <!-- Loading spinner -->
                    <div id="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50" style="display: none;">
                        <div class="text-center">
                            <div class="spinner mx-auto mb-2"></div>
                            <p class="text-gray-600">Cargando equipos...</p>
                        </div>
                    </div>

                    <!-- Info overlay -->
                    <div id="map-info" class="absolute top-4 left-4 bg-white bg-opacity-90 p-3 rounded-lg shadow-md z-40" style="display: none;">
                        <div class="text-sm">
                            <div class="font-semibold">Equipos en mapa:</div>
                            <div id="map-stats">Cargando...</div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <!-- Modal para panorama -->
    <div id="panorama-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg max-w-4xl max-h-[90vh] w-full mx-4 overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-xl font-semibold">🔧 Detalles del Equipo</h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700 text-2xl" type="button">&times;</button>
            </div>
            <div class="p-4">
                <div id="panorama-viewer" class="text-center py-12 bg-gray-50 rounded">
                    <h4 class="text-lg font-medium mb-4">📡 Información del Equipo</h4>
                    <p id="panorama-details" class="text-gray-600 mb-6"></p>
                    <div class="space-x-2">
                        <button class="fi-btn fi-btn--size-md fi-color-primary" type="button">📋 Ver Detalles</button>
                        <button class="fi-btn fi-btn--size-md fi-color-gray" type="button">🔧 Inspeccionar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor de notificaciones -->
    <div id="notifications-container" class="fixed top-4 right-4 z-50 space-y-2"></div>

    @push('scripts')
        <!-- Librerías externas -->
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                crossorigin=""></script>
        <script src="https://unpkg.com/papaparse@5.4.1/papaparse.min.js"></script>

        <!-- Scripts personalizados -->
        @php
            $jsFiles = ['js/data-manager.js', 'js/popup-service.js', 'js/csv-loader.js', 'js/extensions.js', 'js/map.js'];
        @endphp
        @foreach($jsFiles as $jsFile)
            @if(file_exists(public_path($jsFile)))
                <script src="{{ asset($jsFile) }}?v={{ filemtime(public_path($jsFile)) }}"></script>
            @endif
        @endforeach

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🚀 Inicializando aplicación de equipos...');

                // Error handling global
                window.addEventListener('error', function(e) {
                    console.error('💥 Error en la aplicación:', e.error);
                });

                // Verificar dependencias críticas
                if (typeof L === 'undefined') {
                    console.error('❌ Leaflet no está cargado');
                    alert('Error: Librería de mapas no disponible');
                    return;
                }

                // DATOS DE EQUIPOS desde PHP (BD)
                @if(isset($equipos) && $equipos->count() > 0)
                    window.equiposData = @json($equipos);
                console.log('✅ Equipos cargados desde BD:', window.equiposData.length);
                @else
                    window.equiposData = [];
                console.warn('⚠️ No hay equipos en window.equiposData');
                @endif

                // INFORMACIÓN DE LA RUTA
                @if(isset($rutaInfo))
                    window.rutaInfo = @json($rutaInfo);
                console.log('✅ Información de ruta cargada:', window.rutaInfo);
                @else
                    window.rutaInfo = {};
                console.warn('⚠️ No hay información de ruta');
                @endif

                // INFORMACIÓN DE MI RUTA (asignación personal)
                @if(isset($misRuta))
                    window.misRutaData = @json($misRuta);
                @else
                    window.misRutaData = {};
                @endif

                // DEBUG INFO
                @if(isset($debug))
                    window.debugData = @json($debug);
                console.log('🔍 Debug info:', window.debugData);
                @else
                    window.debugData = {};
                @endif

                // Configuración global
                window.mapConfig = {
                    debug: {{ config('app.debug') ? 'true' : 'false' }},
                    csrf_token: '{{ csrf_token() }}',
                    asset_url: '{{ asset('') }}',
                    user_id: {{ auth()->id() ?? 'null' }},
                    user_role: '{{ auth()->user()->role->name ?? 'guest' }}',
                    ruta_id: {{ isset($rutaInfo['id']) ? $rutaInfo['id'] : 'null' }}
                };

                console.log('⚙️ Configuración del mapa:', window.mapConfig);

                // Verificar si existe la función de inicialización
                if (typeof initializeMap === 'function') {
                    console.log('🗺️ Inicializando mapa con initializeMap()...');
                    initializeMap();
                } else if (typeof window.mapController !== 'undefined') {
                    console.log('🗺️ Usando MapController existente...');
                    // El mapa ya debería estar inicializado
                } else {
                    console.error('❌ No se encontró función de inicialización del mapa');
                    console.log('🔍 Funciones disponibles:', Object.keys(window).filter(k => k.includes('map') || k.includes('Map')));
                }

                // Configurar eventos adicionales
                setupAdditionalEvents();

                // Auto-cargar equipos si existen
                setTimeout(() => {
                    if (window.equiposData && window.equiposData.length > 0) {
                        console.log('🚀 Auto-cargando equipos...');
                        if (window.mapController && typeof window.mapController.loadSamplePanoramas === 'function') {
                            window.mapController.loadSamplePanoramas();
                        }
                    }
                }, 1000);

                console.log('✅ Aplicación inicializada correctamente');
            });

            // Configurar eventos adicionales
            function setupAdditionalEvents() {
                // Botón de limpiar mapa
                const clearBtn = document.getElementById('clear-map');
                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        console.log('🧹 Limpiando mapa...');
                        if (window.mapController && typeof window.mapController.clearAllMarkers === 'function') {
                            window.mapController.clearAllMarkers();
                        }
                    });
                }

                // Botón de ajustar vista
                const fitBtn = document.getElementById('fit-map');
                if (fitBtn) {
                    fitBtn.addEventListener('click', function() {
                        console.log('🎯 Ajustando vista del mapa...');
                        if (window.mapController && typeof window.mapController.fitMapToPanoramas === 'function') {
                            window.mapController.fitMapToPanoramas();
                        }
                    });
                }

                // Cerrar modal
                const closeModalBtn = document.getElementById('close-modal');
                if (closeModalBtn) {
                    closeModalBtn.addEventListener('click', function() {
                        document.getElementById('panorama-modal').style.display = 'none';
                    });
                }

                console.log('⚙️ Eventos adicionales configurados');
            }

            // Función helper para mostrar estadísticas en el mapa
            function updateMapStats(equipos) {
                const mapInfo = document.getElementById('map-info');
                const mapStats = document.getElementById('map-stats');

                if (mapInfo && mapStats && equipos && equipos.length > 0) {
                    const stats = `${equipos.length} equipos • Ruta {{ $rutaInfo['id'] ?? 'N/A' }}`;
                    mapStats.textContent = stats;
                    mapInfo.style.display = 'block';
                }
            }

            // Exponer función para uso externo
            window.updateMapStats = updateMapStats;
        </script>
    @endpush
</x-filament-panels::page>
