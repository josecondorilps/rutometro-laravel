<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Mapa de Equipos - LPS Grupo
        </x-slot>

        <x-slot name="headerEnd">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ $totalEquipos['total'] }} equipos en el mapa
            </div>
        </x-slot>

        <div class="space-y-4">
            <!-- Panel de estadísticas -->
            <div class="grid grid-cols-2 md:grid-cols-6 gap-2">
                <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-blue-600">{{ $totalEquipos['total'] }}</div>
                    <div class="text-xs text-blue-600">Total</div>
                </div>

                <div class="bg-green-50 dark:bg-green-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-green-600">{{ $totalEquipos['operativos'] }}</div>
                    <div class="text-xs text-green-600">Operativos</div>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-yellow-600">{{ $totalEquipos['mantenimiento'] }}</div>
                    <div class="text-xs text-yellow-600">Mantenimiento</div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-red-600">{{ $totalEquipos['fuera_servicio'] }}</div>
                    <div class="text-xs text-red-600">Fuera Servicio</div>
                </div>

                <div class="bg-emerald-50 dark:bg-emerald-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-emerald-600">{{ $totalEquipos['inspeccionados'] }}</div>
                    <div class="text-xs text-emerald-600">Inspeccionados</div>
                </div>

                <div class="bg-orange-50 dark:bg-orange-900/20 p-3 rounded-lg text-center">
                    <div class="text-lg font-bold text-orange-600">{{ $totalEquipos['pendientes'] }}</div>
                    <div class="text-xs text-orange-600">Pendientes</div>
                </div>
            </div>

            <!-- Controles del mapa -->
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <div class="flex flex-wrap gap-2">
                    <x-filament::button
                        wire:click="filtrarPorEstado('todos')"
                        size="sm"
                        color="{{ $filtroEstado === 'todos' ? 'primary' : 'gray' }}"
                    >
                        Todos
                    </x-filament::button>

                    <x-filament::button
                        wire:click="filtrarPorEstado('operativo')"
                        size="sm"
                        color="{{ $filtroEstado === 'operativo' ? 'success' : 'gray' }}"
                    >
                        Operativos
                    </x-filament::button>

                    <x-filament::button
                        wire:click="filtrarPorEstado('mantenimiento')"
                        size="sm"
                        color="{{ $filtroEstado === 'mantenimiento' ? 'warning' : 'gray' }}"
                    >
                        Mantenimiento
                    </x-filament::button>

                    <x-filament::button
                        wire:click="filtrarPorEstado('fuera_servicio')"
                        size="sm"
                        color="{{ $filtroEstado === 'fuera_servicio' ? 'danger' : 'gray' }}"
                    >
                        Fuera Servicio
                    </x-filament::button>

                    <x-filament::button
                        wire:click="filtrarPorEstado('inspeccionados')"
                        size="sm"
                        color="{{ $filtroEstado === 'inspeccionados' ? 'info' : 'gray' }}"
                    >
                        Inspeccionados
                    </x-filament::button>

                    <x-filament::button
                        wire:click="filtrarPorEstado('pendientes')"
                        size="sm"
                        color="{{ $filtroEstado === 'pendientes' ? 'secondary' : 'gray' }}"
                    >
                        Pendientes
                    </x-filament::button>
                </div>

                <div class="flex gap-2">
                    <x-filament::button
                        wire:click="toggleMostrarRuta"
                        size="sm"
                        color="gray"
                    >
                        {{ $mostrarRuta ? 'Ocultar Ruta' : 'Mostrar Ruta' }}
                    </x-filament::button>

                    <x-filament::button
                        wire:click="toggleMostrarCobertura"
                        size="sm"
                        color="gray"
                    >
                        {{ $mostrarCobertura ? 'Ocultar Cobertura' : 'Mostrar Cobertura' }}
                    </x-filament::button>

                    <x-filament::button
                        wire:click="resetearFiltros"
                        size="sm"
                        color="gray"
                    >
                        Resetear
                    </x-filament::button>
                </div>
            </div>

            <!-- Contenedor del mapa -->
            <div
                id="mapa-equipos-{{ $this->getId() }}"
                class="w-full h-96 bg-gray-100 dark:bg-gray-800 rounded-lg border shadow-inner"
            ></div>

            <!-- Leyenda -->
            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                <h4 class="font-medium mb-2 text-sm">Leyenda del Mapa</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span>Operativo + Inspeccionado</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                        <span>Operativo - Sin inspeccionar</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-orange-500 rounded-full"></div>
                        <span>En Mantenimiento</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                        <span>Fuera de Servicio</span>
                    </div>
                </div>

                <div class="mt-2 pt-2 border-t text-xs text-gray-600">
                    <p><strong>Controles:</strong> Click en marcadores para detalles | Arrastra para mover | Rueda para zoom</p>
                </div>
            </div>
        </div>
    </x-filament::section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initMapaEquipos{{ $this->getId() }}();
            });

            // Variables globales para este widget
            let map{{ $this->getId() }} = null;
            let markers{{ $this->getId() }} = [];
            let routePolyline{{ $this->getId() }} = null;
            let coverageCircles{{ $this->getId() }} = [];
            let markerClusterGroup{{ $this->getId() }} = null;

            function initMapaEquipos{{ $this->getId() }}() {
                const mapId = 'mapa-equipos-{{ $this->getId() }}';
                const mapContainer = document.getElementById(mapId);

                if (!mapContainer || typeof L === 'undefined') {
                    console.warn('Leaflet no está cargado o el contenedor no existe');
                    return;
                }

                // Limpiar mapa anterior si existe
                if (map{{ $this->getId() }}) {
                    map{{ $this->getId() }}.remove();
                }

                // Inicializar mapa centrado en México
                map{{ $this->getId() }} = L.map(mapId).setView([19.4326, -99.1332], 6);

                // Capas de mapa
                const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors'
                });

                const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: '© <a href="https://www.esri.com/">Esri</a>'
                });

                const terrainLayer = L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors, SRTM'
                });

                // Agregar capa base
                osmLayer.addTo(map{{ $this->getId() }});

                // Control de capas
                const baseMaps = {
                    "Mapa": osmLayer,
                    "Satelite": satelliteLayer,
                    "Terreno": terrainLayer
                };

                L.control.layers(baseMaps).addTo(map{{ $this->getId() }});

                // Inicializar clustering
                if (typeof L.markerClusterGroup !== 'undefined') {
                    markerClusterGroup{{ $this->getId() }} = L.markerClusterGroup({
                        chunkedLoading: true,
                        spiderfyOnMaxZoom: true,
                        showCoverageOnHover: false,
                        zoomToBoundsOnClick: true
                    });
                    map{{ $this->getId() }}.addLayer(markerClusterGroup{{ $this->getId() }});
                }

                // Cargar marcadores
                actualizarMarcadores{{ $this->getId() }}();
            }

            function actualizarMarcadores{{ $this->getId() }}() {
                // Limpiar marcadores existentes
                if (markerClusterGroup{{ $this->getId() }}) {
                    markerClusterGroup{{ $this->getId() }}.clearLayers();
                } else {
                    markers{{ $this->getId() }}.forEach(marker => map{{ $this->getId() }}.removeLayer(marker));
                }
                markers{{ $this->getId() }} = [];

                // Limpiar polilínea de ruta
                if (routePolyline{{ $this->getId() }}) {
                    map{{ $this->getId() }}.removeLayer(routePolyline{{ $this->getId() }});
                    routePolyline{{ $this->getId() }} = null;
                }

                // Limpiar círculos de cobertura
                coverageCircles{{ $this->getId() }}.forEach(circle => map{{ $this->getId() }}.removeLayer(circle));
                coverageCircles{{ $this->getId() }} = [];

                // Datos de equipos desde PHP
                const equipos = @json($equipos);
                const bounds = [];
                const routePoints = [];

                console.log(`Cargando ${equipos.length} equipos en el mapa`);

                // Crear marcadores para cada equipo
                equipos.forEach(function(equipo) {
                    if (!equipo.latitud || !equipo.longitud) {
                        console.warn(`Equipo ${equipo.identificador} sin coordenadas`);
                        return;
                    }

                    const lat = parseFloat(equipo.latitud);
                    const lng = parseFloat(equipo.longitud);

                    // Validar coordenadas
                    if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) {
                        console.warn(`Coordenadas inválidas para equipo ${equipo.identificador}: ${lat}, ${lng}`);
                        return;
                    }

                    // Color del marcador según estado e inspección
                    let iconColor = getMarkerColor(equipo);

                    // Crear icono personalizado con número de orden
                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `
                        <div style="
                            background-color: ${iconColor};
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 3px 6px rgba(0,0,0,0.4);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 11px;
                            color: white;
                            font-weight: bold;
                            cursor: pointer;
                        ">
                            ${equipo.orden_en_ruta || '?'}
                        </div>
                    `,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15],
                        popupAnchor: [0, -15]
                    });

                    // Crear marcador
                    const marker = L.marker([lat, lng], {
                        icon: icon,
                        title: `${equipo.identificador} - ${equipo.estado}`
                    });

                    // Popup con información del equipo
                    const popupContent = createPopupContent(equipo);
                    marker.bindPopup(popupContent, {
                        maxWidth: 350,
                        className: 'custom-popup'
                    });

                    // Tooltip para hover
                    marker.bindTooltip(
                        `${equipo.identificador} - ${equipo.inspeccionado ? 'Inspeccionado' : 'Pendiente'}`,
                        {
                            direction: 'top',
                            offset: [0, -20]
                        }
                    );

                    // Event listeners
                    marker.on('click', function() {
                        console.log('Equipo clickeado:', equipo.id);
                    });

                    // Agregar al clustering o al mapa directamente
                    if (markerClusterGroup{{ $this->getId() }}) {
                        markerClusterGroup{{ $this->getId() }}.addLayer(marker);
                    } else {
                        marker.addTo(map{{ $this->getId() }});
                    }

                    markers{{ $this->getId() }}.push(marker);
                    bounds.push([lat, lng]);

                    // Agregar a puntos de ruta si tiene orden
                    if (equipo.orden_en_ruta) {
                        routePoints.push({
                            coords: [lat, lng],
                            orden: equipo.orden_en_ruta,
                            equipo: equipo
                        });
                    }

                    // Crear círculo de cobertura para antenas/repetidores
                    const mostrarCobertura = @json($mostrarCobertura);
                    if ((equipo.tipo === 'antena' || equipo.tipo === 'repetidor') && mostrarCobertura) {
                        const coverageRadius = equipo.tipo === 'antena' ? 2000 : 1500;
                        const circle = L.circle([lat, lng], {
                            color: iconColor,
                            fillColor: iconColor,
                            fillOpacity: 0.1,
                            radius: coverageRadius,
                            weight: 2
                        });

                        circle.bindPopup(`Área de cobertura - ${equipo.identificador}`);
                        circle.addTo(map{{ $this->getId() }});
                        coverageCircles{{ $this->getId() }}.push(circle);
                    }
                });

                // Crear polilínea de ruta si hay puntos
                const mostrarRuta = @json($mostrarRuta);
                if (routePoints.length > 1 && mostrarRuta) {
                    // Ordenar puntos por orden en ruta
                    routePoints.sort((a, b) => a.orden - b.orden);
                    const coords = routePoints.map(point => point.coords);

                    routePolyline{{ $this->getId() }} = L.polyline(coords, {
                        color: '#3b82f6',
                        weight: 4,
                        opacity: 0.8,
                        dashArray: '10, 5',
                        lineJoin: 'round'
                    }).addTo(map{{ $this->getId() }});

                    routePolyline{{ $this->getId() }}.bindPopup(`
                    <div class="text-center">
                        <h3 class="font-bold">Ruta de Inspección LPS Grupo</h3>
                        <p class="text-sm">${routePoints.length} equipos en secuencia</p>
                    </div>
                `);
                }

                // Ajustar vista para mostrar todos los marcadores
                if (bounds.length > 0) {
                    if (bounds.length === 1) {
                        map{{ $this->getId() }}.setView(bounds[0], 15);
                    } else {
                        const group = new L.featureGroup(markers{{ $this->getId() }});
                        map{{ $this->getId() }}.fitBounds(group.getBounds().pad(0.1));
                    }
                }

                console.log(`Mapa actualizado con ${markers{{ $this->getId() }}.length} marcadores`);
            }

            function getMarkerColor(equipo) {
                // Prioridad: inspección > estado
                if (!equipo.inspeccionado) {
                    switch(equipo.estado) {
                        case 'operativo': return '#eab308'; // amarillo - operativo sin inspeccionar
                        case 'mantenimiento': return '#f97316'; // naranja
                        case 'fuera_servicio': return '#ef4444'; // rojo
                        default: return '#6b7280'; // gris
                    }
                }

                switch(equipo.estado) {
                    case 'operativo': return '#22c55e'; // verde - operativo e inspeccionado
                    case 'mantenimiento': return '#f97316'; // naranja
                    case 'fuera_servicio': return '#ef4444'; // rojo
                    default: return '#8b5cf6'; // violeta
                }
            }

            function createPopupContent(equipo) {
                const estadoClasses = getEstadoClasses(equipo.estado);
                const inspeccionIcon = equipo.inspeccionado ? 'Si' : 'No';
                const inspeccionText = equipo.inspeccionado ? 'Inspeccionado' : 'Pendiente';

                return `
                <div class="p-3 min-w-[280px] max-w-[320px]">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-bold text-lg">${equipo.identificador}</h3>
                        <div class="flex gap-1">
                            <span class="text-xs px-2 py-1 rounded-full ${estadoClasses}">
                                ${getEstadoText(equipo.estado)}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full ${equipo.inspeccionado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${inspeccionText}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <p><strong>Tipo:</strong> ${equipo.tipo || 'N/A'}</p>
                                <p><strong>Area:</strong> ${equipo.area || 'N/A'}</p>
                            </div>
                            <div>
                                <p><strong>Altitud:</strong> ${equipo.altitud || 'N/A'}m</p>
                                <p><strong>Orden:</strong> ${equipo.orden_en_ruta || 'N/A'}</p>
                            </div>
                        </div>

                        ${equipo.ruta ? `<p><strong>Ruta:</strong> ${equipo.ruta.nombre}</p>` : ''}

                        ${equipo.fecha_inspeccion ? `
                        <div class="border-t pt-2">
                            <p><strong>Inspeccion:</strong> ${new Date(equipo.fecha_inspeccion).toLocaleDateString('es-ES')}</p>
                            ${equipo.inspector ? `<p><strong>Inspector:</strong> ${equipo.inspector.name}</p>` : ''}
                        </div>
                        ` : ''}

                        ${equipo.observaciones_campo ? `
                        <div class="border-t pt-2">
                            <p><strong>Observaciones:</strong></p>
                            <p class="text-xs text-gray-600 italic">${equipo.observaciones_campo}</p>
                        </div>
                        ` : ''}
                    </div>

                    <div class="mt-3 pt-2 border-t text-center">
                        <p class="text-xs text-gray-500 mb-2">
                            ${parseFloat(equipo.latitud).toFixed(6)}, ${parseFloat(equipo.longitud).toFixed(6)}
                        </p>
                        <div class="flex gap-2 justify-center">
                            <button onclick="window.open('https://www.google.com/maps?q=${equipo.latitud},${equipo.longitud}', '_blank')"
                                    class="text-xs px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Ver en Google Maps
                            </button>
                            <button onclick="centrarEnEquipo(${equipo.id})"
                                    class="text-xs px-2 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                Centrar Aqui
                            </button>
                        </div>
                    </div>
                </div>
            `;
            }

            function getEstadoClasses(estado) {
                switch(estado) {
                    case 'operativo': return 'bg-green-100 text-green-800';
                    case 'mantenimiento': return 'bg-yellow-100 text-yellow-800';
                    case 'fuera_servicio': return 'bg-red-100 text-red-800';
                    case 'pendiente': return 'bg-gray-100 text-gray-800';
                    default: return 'bg-blue-100 text-blue-800';
                }
            }

            function getEstadoText(estado) {
                switch(estado) {
                    case 'operativo': return 'Operativo';
                    case 'mantenimiento': return 'Mantenimiento';
                    case 'fuera_servicio': return 'Fuera Servicio';
                    case 'pendiente': return 'Pendiente';
                    default: return estado;
                }
            }

            // Función global para centrar en equipo desde popup
            function centrarEnEquipo(equipoId) {
                // Obtener los datos de equipos que ya están en JavaScript
                const equipos = @json($equipos);

                // Buscar el equipo específico por ID
                const equipo = equipos.find(e => e.id == equipoId);

                if (!equipo) {
                    console.warn(`Equipo con ID ${equipoId} no encontrado`);
                    return;
                }

                if (!equipo.latitud || !equipo.longitud) {
                    console.warn(`Equipo ${equipo.identificador} no tiene coordenadas válidas`);
                    return;
                }

                const lat = parseFloat(equipo.latitud);
                const lng = parseFloat(equipo.longitud);

                // Validar que las coordenadas sean números válidos
                if (isNaN(lat) || isNaN(lng)) {
                    console.warn(`Coordenadas inválidas para equipo ${equipo.identificador}`);
                    return;
                }

                // Centrar el mapa en las coordenadas del equipo
                map{{ $this->getId() }}.setView([lat, lng], 16); // Zoom nivel 16 para vista detallada

                // Opcional: Encontrar y abrir el popup del marcador correspondiente
                markers{{ $this->getId() }}.forEach(marker => {
                    // Verificar si este marcador corresponde al equipo buscado
                    if (marker.options.title && marker.options.title.includes(equipo.identificador)) {
                        // Abrir el popup del marcador
                        setTimeout(() => {
                            marker.openPopup();
                        }, 500); // Pequeño delay para que la animación del centrado termine
                    }
                });

                // Opcional: Destacar temporalmente el marcador con una animación
                destacarMarcador(equipo);
            }
            function destacarMarcador(equipo) {
                // Crear un círculo temporal de destaque
                const lat = parseFloat(equipo.latitud);
                const lng = parseFloat(equipo.longitud);

                const destacarCircle = L.circle([lat, lng], {
                    color: '#ff0000',
                    fillColor: '#ff0000',
                    fillOpacity: 0.2,
                    radius: 100,
                    weight: 3
                }).addTo(map{{ $this->getId() }});

                // Remover el círculo después de 3 segundos
                setTimeout(() => {
                    map{{ $this->getId() }}.removeLayer(destacarCircle);
                }, 3000);
            }
            // Escuchar eventos de Livewire
            document.addEventListener('livewire:initialized', function() {
                if (typeof Livewire !== 'undefined') {

                    // Evento para alternar círculos de cobertura
                    Livewire.on('toggleCirculosCobertura', function(event) {
                        coverageCircles{{ $this->getId() }}.forEach(circle => {
                            if (event.mostrar) {
                                circle.addTo(map{{ $this->getId() }});
                            } else {
                                map{{ $this->getId() }}.removeLayer(circle);
                            }
                        });
                    });

                    // Evento para centrar en punto específico
                    Livewire.on('centrarEnPunto', function(event) {
                        if (map{{ $this->getId() }} && event.lat && event.lng) {
                            map{{ $this->getId() }}.setView([event.lat, event.lng], event.zoom || 15);
                        }
                    });

                    // Evento para alternar ruta
                    Livewire.on('toggleRuta', function(event) {
                        if (event.mostrar && routePolyline{{ $this->getId() }}) {
                            routePolyline{{ $this->getId() }}.addTo(map{{ $this->getId() }});
                        } else if (routePolyline{{ $this->getId() }}) {
                            map{{ $this->getId() }}.removeLayer(routePolyline{{ $this->getId() }});
                        }
                    });

                    // Evento para actualizar mapa completo
                    Livewire.on('actualizarMapa', function() {
                        actualizarMarcadores{{ $this->getId() }}();
                    });

                    // Evento para resetear mapa
                    Livewire.on('resetearMapa', function() {
                        // Limpiar filtros visuales
                        if (routePolyline{{ $this->getId() }}) {
                            map{{ $this->getId() }}.removeLayer(routePolyline{{ $this->getId() }});
                        }

                        coverageCircles{{ $this->getId() }}.forEach(circle => {
                            map{{ $this->getId() }}.removeLayer(circle);
                        });

                        // Recargar marcadores
                        actualizarMarcadores{{ $this->getId() }}();
                    });
                }
            });

            // Estilos CSS para popups personalizados
            const style = document.createElement('style');
            style.textContent = `
            .custom-popup .leaflet-popup-content-wrapper {
                border-radius: 8px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            }
            .custom-popup .leaflet-popup-tip {
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .custom-marker {
                filter: drop-shadow(0 3px 6px rgba(0,0,0,0.3));
            }
        `;
            document.head.appendChild(style);
        </script>
    @endpush
</x-filament-widgets::widget>
