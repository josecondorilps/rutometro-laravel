/**
 * Controlador principal del mapa
 */
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

        // Simular carga asíncrona
        setTimeout(() => {
            const samplePanoramas = [
                {
                    id: 1,
                    latitude: -12.0464,
                    longitude: -77.0428,
                    filename: "Lima_Centro_360.jpg",
                    address: "Plaza Mayor, Lima Centro",
                    thumbnail: "https://via.placeholder.com/200x120/2196F3/white?text=Lima+Centro"
                },
                {
                    id: 2,
                    latitude: -12.1211,
                    longitude: -77.0280,
                    filename: "San_Isidro_360.jpg",
                    address: "Av. El Bosque, San Isidro",
                    thumbnail: "https://via.placeholder.com/200x120/4CAF50/white?text=San+Isidro"
                },
                {
                    id: 3,
                    latitude: -12.0988,
                    longitude: -77.0347,
                    filename: "Miraflores_360.jpg",
                    address: "Malecón de Miraflores",
                    thumbnail: "https://via.placeholder.com/200x120/FF9800/white?text=Miraflores"
                },
                {
                    id: 4,
                    latitude: -12.0719,
                    longitude: -77.0474,
                    filename: "Barranco_360.jpg",
                    address: "Puente de los Suspiros, Barranco",
                    thumbnail: "https://via.placeholder.com/200x120/9C27B0/white?text=Barranco"
                },
                {
                    id: 5,
                    latitude: -12.0629,
                    longitude: -77.0365,
                    filename: "Pueblo_Libre_360.jpg",
                    address: "Av. La Marina, Pueblo Libre",
                    thumbnail: "https://via.placeholder.com/200x120/F44336/white?text=Pueblo+Libre"
                }
            ];

            window.dataManager.setPanoramas(samplePanoramas);
            loading.classList.add('hidden');

            // Auto-fit al cargar los datos
            this.fitMapToPanoramas();

        }, 1000);
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
            item.classList.toggle('active', item.dataset.panoramaId == panoramaId);
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
