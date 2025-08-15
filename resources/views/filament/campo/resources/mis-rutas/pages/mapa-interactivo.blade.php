{{-- resources/views/filament/resources/rutas/pages/mapa-interactivo.blade.php --}}
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
            }
            .main-content {
                border-radius: 0.75rem;
                overflow: hidden;
                min-height: 600px;
            }
            @media (max-width: 768px) {
                .controls { flex-direction: column; gap: 0.5rem; }
                .sidebar { width: 100%; border-right: none; }
                .flex.h-\[600px\] { flex-direction: column; height: auto; }
                #map { height: 400px; }
            }
            .spinner {
                border-radius: 50%;
                width: 2rem; height: 2rem;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    @endpush

    <div class="app-container">
        <!-- Header -->
        <header class="app-header">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
                <div class="controls flex flex-wrap gap-2">
                    <button id="load-panoramas" class="fi-btn fi-btn--size-md fi-color-primary" type="button">
                        📍 Cargar Panoramas
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
                            <p class="text-gray-600">Cargando datos...</p>
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
                <h3 class="text-xl font-semibold">Visor Panorama 360°</h3>
                <button id="close-modal" class="text-gray-500 hover:text-gray-700 text-2xl" type="button">&times;</button>
            </div>
            <div class="p-4">
                <div id="panorama-viewer" class="text-center py-12 bg-gray-50 rounded">
                    <h4 class="text-lg font-medium mb-4">🌐 Visor 360° Placeholder</h4>
                    <p id="panorama-details" class="text-gray-600 mb-6"></p>
                    <div class="space-x-2">
                        <button class="fi-btn fi-btn--size-md fi-color-primary" type="button">🔄 Rotar</button>
                        <button class="fi-btn fi-btn--size-md fi-color-gray" type="button">🔍 Zoom</button>
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
                // Error handling
                window.addEventListener('error', function(e) {
                    console.error('Error en la aplicación:', e.error);
                });

                // Verificar dependencias
                if (typeof L === 'undefined') {
                    console.error('Leaflet no está cargado');
                    return;
                }

                // Datos de rutas
                @if(isset($rutas) && $rutas->count() > 0)
                    window.rutasData = @json($rutas);
                @else
                    window.rutasData = [];
                @endif

                // Configuración
                window.mapConfig = {
                    debug: {{ config('app.debug') ? 'true' : 'false' }},
                    csrf_token: '{{ csrf_token() }}',
                    asset_url: '{{ asset('') }}'
                };

                // Inicializar mapa
                if (typeof initializeMap === 'function') {
                    initializeMap();
                } else {
                    console.error('Función initializeMap no encontrada');
                }
            });
        </script>
    @endpush
</x-filament-panels::page>
