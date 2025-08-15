class MapController {
    constructor() {
        this.map = null;
        this.panoramaLayerGroup = null;
        this.csvLayerGroup = null;
        this.currentMarker = null;
        this.selectedPanoramaMarker = null;
        this.isInitialized = false;

        // Configuración por defecto
        this.config = {
            defaultCenter: [-12.0464, -77.0428], // Lima, Perú
            defaultZoom: 12,
            maxZoom: 18,
            minZoom: 5
        };

        // Configurar eventos
        this.setupEventListeners();

        // Suscribirse a cambios de datos
        this.subscribeToDataChanges();
    }

    // Inicializar el mapa
    initMap() {
        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        console.log('Inicializando mapa...');

        // Crear el mapa
        this.map = L.map('map', {
            center: this.config.defaultCenter,
            zoom: this.config.defaultZoom,
            zoomControl: true,
            attributionControl: true,
            preferCanvas: true
        });

        // Añadir capa base
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: this.config.maxZoom,
            minZoom: this.config.minZoom,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(this.map);

        // Eventos del mapa
        this.map.on('zoomend moveend', () => {
            const center = this.map.getCenter();
            const zoom = this.map.getZoom();
            console.log(`Mapa - Centro: [${center.lat.toFixed(6)}, ${center.lng.toFixed(6)}], Zoom: ${zoom}`);
        });

        this.map.on('click', (e) => {
            console.log(`Click en mapa: [${e.latlng.lat.toFixed(6)}, ${e.latlng.lng.toFixed(6)}]`);
        });

        this.isInitialized = true;
        console.log('Mapa inicializado correctamente');
    }

    // Configurar event listeners
    setupEventListeners() {
        // Botones principales
        document.getElementById('load-panoramas')?.addEventListener('click', () => {
            this.loadSamplePanoramas();
        });

        document.getElementById('clear-map')?.addEventListener('click', () => {
            this.clearAllMarkers();
        });

        // Navegación
        document.getElementById('prev-point')?.addEventListener('click', () => {
            this.navigateToPrevious();
        });

        document.getElementById('next-point')?.addEventListener('click', () => {
            this.navigateToNext();
        });

        // Teclas de navegación
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) return; // Evitar interferir con atajos del navegador

            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.navigateToPrevious();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.navigateToNext();
                    break;
                case 'Escape':
                    this.clearSelection();
                    break;
            }
        });

        // Redimensionamiento de ventana
        window.addEventListener('resize', () => {
            if (this.map) {
                this.map.invalidateSize();
            }
        });
    }

    // Suscribirse a cambios en DataManager
    subscribeToDataChanges() {
        // Cambios en panoramas
        window.dataManager.subscribe('dataUpdate', (data) => {
            if (data.type === 'panoramas') {
                this.updatePanoramaMarkers();
                this.updateLocationList(data.data);
            } else if (data.type === 'csv') {
                this.updateCsvMarkers();
            }
        });

        // Cambios en panorama actual
        window.dataManager.subscribe('panoramaChange', (data) => {
            this.updateSelectedMarker(data.current);
            this.updateNavigationControls();
            this.updateCurrentInfo(data.panorama);
        });

        // Cambios en destacados
        window.dataManager.subscribe('highlightChange', () => {
            this.updateHighlightedMarkers();
        });
    }

    // Cargar panoramas de ejemplo

    loadSamplePanoramas() {
        const loading = document.getElementById('loading');
        loading.classList.remove('hidden');

        setTimeout(() => {
            try {
                // OBTENER EQUIPOS desde PHP (como antes, pero ahora desde tu BD)
                const equiposData = window.equiposData || [];
                const rutaInfo = window.rutaInfo || {};

                console.log('🔍 DEBUG - Cargando equipos desde BD:');
                console.log('- Total equipos desde PHP:', equiposData.length);
                console.log('- Ruta info:', rutaInfo);
                console.log('- Equipos data:', equiposData);

                if (!equiposData || equiposData.length === 0) {
                    console.warn('❌ NO HAY EQUIPOS - Array está vacío');
                    this.showNotification(`❌ No hay equipos en la ruta: "${rutaInfo.nombre}"`, 'warning');
                    loading.classList.add('hidden');
                    return;
                }

                // CONVERTIR EQUIPOS AL FORMATO QUE ESPERA TU SISTEMA ORIGINAL
                const panoramasFormateados = equiposData.map((equipo, index) => {
                    console.log(`🔧 Convirtiendo equipo ${index + 1}:`, {
                        id: equipo.id,
                        identificador: equipo.identificador,
                        latitud: equipo.latitude,
                        longitud: equipo.longitude
                    });

                    const lat = parseFloat(equipo.latitude);
                    const lng = parseFloat(equipo.longitude);

                    if (isNaN(lat) || isNaN(lng)) {
                        console.warn(`⚠️ Coordenadas inválidas para ${equipo.identificador}`);
                        return null;
                    }

                    // FORMATO EXACTO que espera tu MapController original
                    return {
                        id: equipo.id,                    // ← ID único
                        latitude: lat,                    // ← Coordenada Y
                        longitude: lng,                   // ← Coordenada X
                        filename: equipo.filename || `${equipo.identificador}_360.jpg`,  // ← Nombre archivo
                        address: equipo.address || equipo.direccion || `Equipo ${equipo.identificador}`, // ← Dirección
                        thumbnail: equipo.thumbnail || this.generateEquipoThumbnail(equipo), // ← Thumbnail

                        // Información adicional para popups
                        identificador: equipo.identificador,
                        tipo: equipo.tipo,
                        estado: equipo.estado,
                        area: equipo.area,
                        orden_en_ruta: equipo.orden_en_ruta,
                        inspeccionado: equipo.inspeccionado,
                        observaciones: equipo.observaciones
                    };
                }).filter(panorama => panorama !== null); // Filtrar nulls

                console.log('📍 PANORAMAS FORMATEADOS PARA EL MAPA:');
                console.log(`- Equipos procesados: ${equiposData.length}`);
                console.log(`- Panoramas válidos: ${panoramasFormateados.length}`);
                console.log('- Panoramas:', panoramasFormateados.map(p => ({
                    id: p.id,
                    coords: [p.latitude, p.longitude],
                    filename: p.filename
                })));

                if (panoramasFormateados.length === 0) {
                    console.error('❌ NO SE CREARON PANORAMAS VÁLIDOS');
                    this.showNotification('❌ No se pudieron procesar los equipos como marcadores', 'error');
                    loading.classList.add('hidden');
                    return;
                }

                // USAR TU SISTEMA ORIGINAL - Llamar a dataManager.setPanoramas()
                console.log('🗺️ Enviando panoramas al DataManager...');
                window.dataManager.setPanoramas(panoramasFormateados);

                loading.classList.add('hidden');

                // Mensaje de éxito
                this.showNotification(
                    `✅ ${panoramasFormateados.length} equipos cargados como marcadores en el mapa`,
                    'success'
                );

                // Auto-ajustar mapa (tu función original)
                this.fitMapToPanoramas();

                console.log('🎯 EQUIPOS CARGADOS EXITOSAMENTE EN EL MAPA');
                console.log('- Cada equipo ahora aparece como un marcador');
                console.log('- El sistema usa tu MapController original');

            } catch (error) {
                console.error('💥 ERROR cargando equipos:', error);
                loading.classList.add('hidden');

                this.showNotification(`💥 Error: ${error.message}`, 'error');

                // Debug en caso de error
                console.log('🔍 DEBUG ERROR:');
                console.log('- window.equiposData:', window.equiposData);
                console.log('- window.rutaInfo:', window.rutaInfo);
                console.log('- window.dataManager:', window.dataManager);
            }
        }, 500);
    }

// Generar thumbnail para equipos (helper function)
    generateEquipoThumbnail(equipo) {
        const coloresPorTipo = {
            'antena': '2196F3',       // Azul
            'repetidor': '4CAF50',    // Verde
            'base': 'FF9800',         // Naranja
            'movil': '9C27B0',        // Púrpura
            'tower': '607D8B',        // Azul gris
            'sensor': '00BCD4',       // Cian
            'default': '9E9E9E'       // Gris
        };

        const color = coloresPorTipo[equipo.tipo] || coloresPorTipo['default'];
        const texto = encodeURIComponent(equipo.identificador || 'EQ');

        return `https://via.placeholder.com/200x120/${color}/white?text=${texto}`;
    }
    buildEquipoDescription(equipo) {
        const partes = [];

        partes.push(`🆔 <strong>Identificador:</strong> ${equipo.identificador}`);

        if (equipo.tipo) {
            partes.push(`🔧 <strong>Tipo:</strong> ${equipo.tipo.toUpperCase()}`);
        }

        if (equipo.estado) {
            const estadoIcon = equipo.estado === 'activo' ? '✅' : (equipo.estado === 'inactivo' ? '❌' : '⚠️');
            partes.push(`${estadoIcon} <strong>Estado:</strong> ${equipo.estado.toUpperCase()}`);
        }

        if (equipo.area) {
            partes.push(`📍 <strong>Área:</strong> ${equipo.area}`);
        }

        if (equipo.altitud) {
            partes.push(`⛰️ <strong>Altitud:</strong> ${equipo.altitud}m`);
        }

        if (equipo.orden_en_ruta) {
            partes.push(`🔢 <strong>Orden en ruta:</strong> ${equipo.orden_en_ruta}`);
        }

        // Estado de inspección
        if (equipo.inspeccionado) {
            partes.push(`✅ <strong>Inspeccionado:</strong> ${equipo.fecha_inspeccion || 'Sí'}`);
            if (equipo.observaciones) {
                partes.push(`📝 <strong>Observaciones:</strong> ${equipo.observaciones}`);
            }
        } else {
            partes.push(`⏳ <strong>Estado:</strong> Pendiente de inspección`);
        }

        return partes.join('<br>');
    }


// Función de fallback en caso de error
    loadFallbackEquipos() {
        console.warn('Cargando equipos de fallback...');
        const fallbackEquipos = [
            {
                id: 'fallback_1',
                latitude: -12.0464,
                longitude: -77.0428,
                filename: "Equipo_Fallback_360.jpg",
                address: "Equipo de ejemplo - Datos no disponibles",
                thumbnail: this.generateFallbackThumbnail('ejemplo'),
                tipo: 'ejemplo',
                estado: 'activo',
                nombre: 'Equipo de Ejemplo'
            }
        ];

        window.dataManager.setPanoramas(fallbackEquipos);
        this.showNotification('Se cargaron equipos de ejemplo (datos no disponibles)', 'warning');
    }

// Función para mostrar información de la ruta
    showRutaInfo(rutaInfo) {
        const infoText = [];

        if (rutaInfo.total_equipos) {
            infoText.push(`📊 Total equipos: ${rutaInfo.total_equipos}`);
        }

        if (rutaInfo.distancia_km) {
            infoText.push(`📏 Distancia: ${rutaInfo.distancia_km} km`);
        }

        if (rutaInfo.tiempo_estimado_minutos) {
            infoText.push(`⏱️ Tiempo estimado: ${rutaInfo.tiempo_estimado_minutos} min`);
        }

        if (rutaInfo.estado) {
            infoText.push(`📋 Estado: ${rutaInfo.estado}`);
        }

        if (infoText.length > 0) {
            this.showNotification(infoText.join(' | '), 'info');
        }
    }

// Función para mostrar notificaciones (reutilizada)
    showNotification(message, type = 'info') {
        const container = document.getElementById('notifications-container');
        if (!container) return;

        const notification = document.createElement('div');
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        }[type] || 'bg-gray-500';

        notification.className = `${bgColor} text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0 max-w-md`;
        notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span class="text-sm">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white/70 hover:text-white ml-2">×</button>
        </div>
    `;

        container.appendChild(notification);

        // Animación de entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);

        // Auto-remover después de 8 segundos para mensajes informativos
        const autoRemoveTime = type === 'info' ? 8000 : 5000;
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, autoRemoveTime);
    }
// Generar thumbnail específico para equipos
    generateEquipoThumbnail(equipo) {
        const coloresPorTipo = {
            'antena': '2196F3',       // Azul
            'repetidor': '4CAF50',    // Verde
            'base': 'FF9800',         // Naranja
            'movil': '9C27B0',        // Púrpura
            'tower': '607D8B',        // Azul gris
            'sensor': '00BCD4',       // Cian
            'unknown': '9E9E9E',      // Gris
        };

        const coloresPorEstado = {
            'activo': null,           // Usar color del tipo
            'inactivo': 'F44336',     // Rojo
            'mantenimiento': 'FF9800', // Naranja
            'error': 'F44336'         // Rojo
        };

        // Determinar color final
        let color = coloresPorEstado[equipo.estado] || coloresPorTipo[equipo.tipo] || coloresPorTipo['unknown'];

        // Texto para el placeholder
        const texto = encodeURIComponent(equipo.identificador || equipo.tipo || 'EQ');

        return `https://via.placeholder.com/200x120/${color}/white?text=${texto}`;
    }

// Mensaje de éxito con información específica de equipos
    buildEquiposMessage(rutaInfo, equiposValidos, equiposTotal) {
        const partes = [
            `🗺️ Ruta: "${rutaInfo.nombre}"`,
            `📍 ${equiposValidos.length}/${equiposTotal.length} equipos cargados`
        ];

        if (rutaInfo.progreso_inspeccion !== undefined) {
            partes.push(`✅ ${rutaInfo.progreso_inspeccion}% inspeccionado`);
        }

        return partes.join(' | ');
    }

// Mostrar estadísticas de inspección
    showInspectionStats(rutaInfo, equipos) {
        const stats = [];

        if (rutaInfo.equipos_inspeccionados !== undefined) {
            stats.push(`✅ Inspeccionados: ${rutaInfo.equipos_inspeccionados}`);
        }

        if (rutaInfo.equipos_pendientes !== undefined) {
            stats.push(`⏳ Pendientes: ${rutaInfo.equipos_pendientes}`);
        }

        if (rutaInfo.tipos_equipos && rutaInfo.tipos_equipos.length > 0) {
            stats.push(`🔧 Tipos: ${rutaInfo.tipos_equipos.join(', ')}`);
        }

        if (rutaInfo.areas_cubiertas && rutaInfo.areas_cubiertas.length > 0) {
            stats.push(`📍 Áreas: ${rutaInfo.areas_cubiertas.join(', ')}`);
        }

        if (stats.length > 0) {
            this.showNotification(stats.join(' | '), 'info');
        }
    }

// Configurar interacciones específicas para equipos
    setupEquipoInteractions(equipos) {
        // Agregar eventos personalizados para equipos
        equipos.forEach(equipo => {
            // Aquí puedes agregar lógica específica como:
            // - Click para abrir detalles del equipo
            // - Hover para mostrar información rápida
            // - Colores diferentes según estado de inspección

            console.log(`🔧 Equipo configurado: ${equipo.identificador} (${equipo.tipo})`);
        });
    }

// Función auxiliar para generar thumbnail de fallback
    generateFallbackThumbnail(tipo) {
        const colors = {
            'antena': '2196F3',
            'repetidor': '4CAF50',
            'base': 'FF9800',
            'movil': '9C27B0',
            'ejemplo': 'E91E63',
            'unknown': '607D8B',
            'default': 'F44336'
        };

        const color = colors[tipo] || colors['default'];
        return `https://via.placeholder.com/200x120/${color}/white?text=${encodeURIComponent(tipo.toUpperCase())}`;
    }

// Función de fallback en caso de error
    loadFallbackEquipos() {
        console.warn('Cargando equipos de fallback...');
        const fallbackEquipos = [
            {
                id: 'fallback_1',
                latitude: -12.0464,
                longitude: -77.0428,
                filename: "Equipo_Fallback_360.jpg",
                address: "Equipo de ejemplo - Datos no disponibles",
                thumbnail: this.generateFallbackThumbnail('ejemplo'),
                tipo: 'ejemplo',
                estado: 'activo',
                nombre: 'Equipo de Ejemplo'
            }
        ];

        window.dataManager.setPanoramas(fallbackEquipos);
        this.showNotification('Se cargaron equipos de ejemplo (datos no disponibles)', 'warning');
    }

// Función para mostrar información de la ruta
    showRutaInfo(rutaInfo) {
        const infoText = [];

        if (rutaInfo.total_equipos) {
            infoText.push(`📊 Total equipos: ${rutaInfo.total_equipos}`);
        }

        if (rutaInfo.distancia_km) {
            infoText.push(`📏 Distancia: ${rutaInfo.distancia_km} km`);
        }

        if (rutaInfo.tiempo_estimado_minutos) {
            infoText.push(`⏱️ Tiempo estimado: ${rutaInfo.tiempo_estimado_minutos} min`);
        }

        if (rutaInfo.estado) {
            infoText.push(`📋 Estado: ${rutaInfo.estado}`);
        }

        if (infoText.length > 0) {
            this.showNotification(infoText.join(' | '), 'info');
        }
    }

// Función para mostrar notificaciones (reutilizada)
    showNotification(message, type = 'info') {
        const container = document.getElementById('notifications-container');
        if (!container) return;

        const notification = document.createElement('div');
        const bgColor = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        }[type] || 'bg-gray-500';

        notification.className = `${bgColor} text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0 max-w-md`;
        notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span class="text-sm">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white/70 hover:text-white ml-2">×</button>
        </div>
    `;

        container.appendChild(notification);

        // Animación de entrada
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 100);

        // Auto-remover después de 8 segundos para mensajes informativos
        const autoRemoveTime = type === 'info' ? 8000 : 5000;
        setTimeout(() => {
            notification.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, autoRemoveTime);
    }

    // Actualizar marcadores de panoramas
    updatePanoramaMarkers() {
        console.log('Actualizando marcadores de panoramas...');

        if (!this.map) {
            console.warn('Mapa no inicializado');
            return;
        }

        const panoramas = window.dataManager.getPanoramas();

        if (!panoramas || panoramas.length === 0) {
            console.log('No hay panoramas para mostrar');
            this.clearPanoramaMarkers();
            return;
        }

        // Limpiar marcadores existentes
        this.clearPanoramaMarkers();

        // Crear nuevo grupo de capas
        this.panoramaLayerGroup = L.layerGroup();

        const highlightedPanoramas = window.dataManager.getHighlighted();
        const currentPanoramaId = window.dataManager.getCurrentPanorama();
        let markersAdded = 0;

        panoramas.forEach((panorama) => {
            if (panorama.latitude && panorama.longitude) {
                // Determinar estilo del marcador
                const isSelected = currentPanoramaId === panorama.id;
                const isHighlighted = highlightedPanoramas.has(panorama.id);

                const markerStyle = this.getMarkerStyle(isSelected, isHighlighted);

                // Crear marcador
                const marker = L.circleMarker(
                    [panorama.latitude, panorama.longitude],
                    markerStyle
                );

                // Guardar referencia si es el seleccionado
                if (isSelected) {
                    this.selectedPanoramaMarker = marker;
                }

                // Añadir popup
                const popupContent = window.popupService.createPanoramaPopup(panorama);
                marker.bindPopup(popupContent, {
                    maxWidth: 300,
                    className: 'custom-popup'
                });

                // Añadir comportamiento hover
                window.popupService.addHoverBehavior(marker, popupContent);

                // Evento click
                marker.on('click', () => {
                    console.log(`Click en panorama ${panorama.id}`);
                    this.selectPanorama(panorama.id);
                });

                // Añadir al grupo
                this.panoramaLayerGroup.addLayer(marker);
                markersAdded++;
            }
        });

        // Añadir al mapa
        if (markersAdded > 0) {
            this.panoramaLayerGroup.addTo(this.map);
            console.log(`${markersAdded} marcadores de panorama añadidos`);
        }
    }

    // Obtener estilo de marcador según estado
    getMarkerStyle(isSelected, isHighlighted) {
        if (isHighlighted) {
            // Verde para panoramas destacados
            return {
                radius: 10,
                color: '#00FF3C',
                fillColor: 'rgba(0, 255, 60, 0.3)',
                fillOpacity: 0.5,
                weight: 5,
                opacity: 1,
                className: 'custom-marker highlighted-marker'
            };
        } else if (isSelected) {
            // Naranja para panorama seleccionado
            return {
                radius: 12,
                color: '#FF4500',
                fillColor: 'rgba(255, 69, 0, 0.3)',
                fillOpacity: 0.5,
                weight: 6,
                opacity: 1,
                className: 'custom-marker selected-marker marker-selected'
            };
        } else {
            // Azul para panorama normal
            return {
                radius: 8,
                color: '#2196F3',
                fillColor: 'rgba(239, 239, 239, 0)',
                fillOpacity: 0.1,
                weight: 4,
                opacity: 1,
                className: 'custom-marker normal-marker'
            };
        }
    }

    // Actualizar marcadores CSV
    updateCsvMarkers() {
        console.log('Actualizando marcadores CSV...');

        if (!this.map) {
            console.warn('Mapa no inicializado para CSV');
            return;
        }

        const csvData = window.dataManager.getCsvData();

        if (!csvData || csvData.length === 0) {
            console.log('No hay datos CSV para mostrar');
            this.clearCsvMarkers();
            return;
        }

        // Limpiar marcadores CSV existentes
        this.clearCsvMarkers();

        // Crear nuevo grupo de capas
        this.csvLayerGroup = L.layerGroup();

        let markersAdded = 0;

        csvData.forEach((location, index) => {
            if (location.latitud && location.longitud) {
                // Crear marcador morado
                const marker = L.circleMarker(
                    [location.latitud, location.longitud],
                    {
                        radius: 8,
                        color: '#800080',
                        fillColor: 'rgba(128, 0, 128, 0.3)',
                        fillOpacity: 0.6,
                        weight: 3,
                        opacity: 1,
                        className: 'custom-marker csv-marker'
                    }
                );

                // Crear popup
                const popupContent = window.popupService.createCsvPopup(location, index);
                marker.bindPopup(popupContent, {
                    maxWidth: 250,
                    className: 'custom-popup csv-popup'
                });

                // Efectos hover
                marker.on('mouseover', () => {
                    marker.setStyle({
                        radius: 10,
                        weight: 4,
                        fillOpacity: 0.8
                    });
                });

                marker.on('mouseout', () => {
                    marker.setStyle({
                        radius: 8,
                        weight: 3,
                        fillOpacity: 0.6
                    });
                });

                // Añadir al grupo
                this.csvLayerGroup.addLayer(marker);
                markersAdded++;
            }
        });

        // Añadir al mapa
        if (markersAdded > 0) {
            this.csvLayerGroup.addTo(this.map);
            console.log(`${markersAdded} marcadores CSV añadidos`);
        }
    }

    // Seleccionar panorama
    selectPanorama(panoramaId) {
        const panorama = window.dataManager.getPanoramaById(panoramaId);
        if (!panorama) {
            console.warn(`Panorama ${panoramaId} no encontrado`);
            return;
        }

        console.log(`Seleccionando panorama ${panoramaId}`);

        // Actualizar data manager
        window.dataManager.setCurrentPanorama(panoramaId);

        // Centrar mapa en el panorama
        this.map.setView([panorama.latitude, panorama.longitude], 15, {
            animate: true,
            duration: 0.5
        });

        // Simular apertura de visor 360 (aquí podrías abrir un modal o navegar)
        this.showPanoramaViewer(panorama);
    }

    // Mostrar visor de panorama (simulado)
    showPanoramaViewer(panorama) {
        // Por ahora solo mostrar alerta, aquí integrarías tu visor 360
        alert(`Abriendo panorama: ${panorama.filename || 'Sin nombre'}\\nCoordenadas: ${panorama.latitude}, ${panorama.longitude}`);
    }

    // Toggle highlight de panorama
    toggleHighlight(panoramaId) {
        const isHighlighted = window.dataManager.isHighlighted(panoramaId);

        if (isHighlighted) {
            window.dataManager.removeHighlighted(panoramaId);
            console.log(`Panorama ${panoramaId} ya no está destacado`);
        } else {
            window.dataManager.addHighlighted(panoramaId);
            console.log(`Panorama ${panoramaId} destacado`);
        }
    }

    // Actualizar marcador seleccionado
    updateSelectedMarker(panoramaId) {
        if (!this.panoramaLayerGroup) return;

        // Reestablecer todos los marcadores
        this.updatePanoramaMarkers();
    }

    // Actualizar marcadores destacados
    updateHighlightedMarkers() {
        if (!this.panoramaLayerGroup) return;

        // Reestablecer todos los marcadores
        this.updatePanoramaMarkers();
    }

    // Navegación
    navigateToNext() {
        const nextPanorama = window.dataManager.getNextPanorama();
        if (nextPanorama) {
            this.selectPanorama(nextPanorama.id);
        }
    }

    navigateToPrevious() {
        const prevPanorama = window.dataManager.getPreviousPanorama();
        if (prevPanorama) {
            this.selectPanorama(prevPanorama.id);
        }
    }

    // Actualizar controles de navegación
    updateNavigationControls() {
        const position = window.dataManager.getCurrentPosition();
        const prevBtn = document.getElementById('prev-point');
        const nextBtn = document.getElementById('next-point');
        const positionSpan = document.getElementById('current-position');

        if (positionSpan) {
            positionSpan.textContent = `${position.current} / ${position.total}`;
        }

        if (prevBtn) {
            prevBtn.disabled = position.total === 0;
        }

        if (nextBtn) {
            nextBtn.disabled = position.total === 0;
        }
    }

    // Actualizar lista de ubicaciones en sidebar
    updateLocationList(panoramas) {
        const locationList = document.getElementById('location-list');
        if (!locationList) return;

        locationList.innerHTML = '';

        panoramas.forEach((panorama) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <strong>${panorama.filename || 'Sin nombre'}</strong><br>
                <small>${panorama.address || 'Sin dirección'}</small>
            `;
            li.dataset.panoramaId = panorama.id;

            li.addEventListener('click', () => {
                this.selectPanorama(panorama.id);
                this.updateLocationListActive(panorama.id);
            });

            locationList.appendChild(li);
        });
    }

    // Actualizar elemento activo en lista
    updateLocationListActive(panoramaId) {
        const items = document.querySelectorAll('#location-list li');
        items.forEach(item => {
            item.classList.toggle('active', item.dataset.panoramaId === panoramaId);
        });
    }

    // Actualizar panel de información
    updateCurrentInfo(panorama) {
        const infoPanel = document.getElementById('current-info');
        if (!infoPanel || !panorama) return;

        infoPanel.innerHTML = `
            <h4>${panorama.filename || 'Sin nombre'}</h4>
            <p><strong>ID:</strong> ${panorama.id}</p>
            <p><strong>Coordenadas:</strong><br>${panorama.latitude.toFixed(6)}, ${panorama.longitude.toFixed(6)}</p>
            ${panorama.address ? `<p><strong>Dirección:</strong><br>${panorama.address}</p>` : ''}
            <button class="btn btn-primary" style="margin-top: 1rem; width: 100%;" onclick="mapController.showPanoramaViewer(${JSON.stringify(panorama).replace(/"/g, '&quot;')})">
                Ver Panorama 360°
            </button>
        `;

        this.updateLocationListActive(panorama.id);
    }

    // Ajustar vista del mapa a los panoramas
    fitMapToPanoramas() {
        const panoramas = window.dataManager.getPanoramas();
        if (!this.map || !panoramas || panoramas.length === 0) return;

        const validPanoramas = panoramas.filter(p => p.latitude && p.longitude);
        if (validPanoramas.length === 0) return;

        if (validPanoramas.length === 1) {
            const p = validPanoramas[0];
            this.map.setView([p.latitude, p.longitude], 15);
        } else {
            const bounds = L.latLngBounds(
                validPanoramas.map(p => [p.latitude, p.longitude])
            );
            this.map.fitBounds(bounds, { padding: [20, 20] });
        }

        console.log('Vista ajustada a panoramas');
    }

    // Ajustar vista incluyendo CSV y panoramas
    fitMapToAllPoints() {
        if (!this.map) return;

        const allPoints = [];

        // Añadir panoramas
        const panoramas = window.dataManager.getPanoramas();
        panoramas.forEach(p => {
            if (p.latitude && p.longitude) {
                allPoints.push([p.latitude, p.longitude]);
            }
        });

        // Añadir puntos CSV
        const csvData = window.dataManager.getCsvData();
        csvData.forEach(location => {
            if (location.latitud && location.longitud) {
                allPoints.push([location.latitud, location.longitud]);
            }
        });

        if (allPoints.length === 0) return;

        if (allPoints.length === 1) {
            this.map.setView(allPoints[0], 15);
        } else {
            const bounds = L.latLngBounds(allPoints);
            this.map.fitBounds(bounds, { padding: [20, 20] });
        }

        console.log(`Vista ajustada a ${allPoints.length} puntos totales`);
    }

    // Limpiar todos los marcadores
    clearAllMarkers() {
        this.clearPanoramaMarkers();
        this.clearCsvMarkers();
        window.dataManager.setPanoramas([]);
        window.dataManager.setCsvData([]);
        window.dataManager.setCurrentPanorama(null);

        // Limpiar UI
        const locationList = document.getElementById('location-list');
        if (locationList) locationList.innerHTML = '';

        const infoPanel = document.getElementById('current-info');
        if (infoPanel) infoPanel.innerHTML = '<p>Selecciona un punto en el mapa</p>';

        console.log('Todos los marcadores limpiados');
    }

    // Limpiar marcadores de panoramas
    clearPanoramaMarkers() {
        if (this.panoramaLayerGroup && this.map) {
            this.map.removeLayer(this.panoramaLayerGroup);
            this.panoramaLayerGroup = null;
            this.selectedPanoramaMarker = null;
        }
    }

    // Limpiar marcadores CSV
    clearCsvMarkers() {
        if (this.csvLayerGroup && this.map) {
            this.map.removeLayer(this.csvLayerGroup);
            this.csvLayerGroup = null;
        }
    }

    // Limpiar selección actual
    clearSelection() {
        window.dataManager.setCurrentPanorama(null);

        const infoPanel = document.getElementById('current-info');
        if (infoPanel) infoPanel.innerHTML = '<p>Selecciona un punto en el mapa</p>';

        this.updateLocationListActive(null);
    }

    showEquipoViewer(equipo) {
        const modal = document.getElementById('panorama-modal');
        const details = document.getElementById('panorama-details');

        if (modal && details) {
            details.innerHTML = `
            <strong>Equipo:</strong> ${equipo.nombre}<br>
            <strong>Tipo:</strong> ${equipo.tipo}<br>
            <strong>Dirección:</strong> ${equipo.address}<br>
            <strong>Coordenadas:</strong> ${equipo.latitude.toFixed(6)}, ${equipo.longitude.toFixed(6)}<br>
            <strong>Estado:</strong> ${equipo.estado}
        `;
            modal.style.display = 'flex';
        }
    }


}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM cargado, inicializando aplicación...');

    // Crear instancia del controlador
    window.mapController = new MapController();

    // Inicializar mapa
    window.mapController.initMap();

    // Mostrar notificación de bienvenida
    setTimeout(() => {
        window.csvLoader.showNotification('Aplicación de mapa cargada correctamente');
    }, 500);

    console.log('Aplicación inicializada');
});


// Cerrar modal
document.getElementById('close-modal')?.addEventListener('click', function() {
    const modal = document.getElementById('panorama-modal');
    if (modal) modal.style.display = 'none';
});
