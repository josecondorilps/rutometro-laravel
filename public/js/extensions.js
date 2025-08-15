class GeolocationService {
    constructor() {
        this.watchId = null;
    }

    getCurrentPosition() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                reject(new Error('Geolocalización no disponible'));
                return;
            }

            navigator.geolocation.getCurrentPosition(
                position => resolve(position.coords),
                error => reject(error),
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 60000
                }
            );
        });
    }

    watchPosition(callback) {
        if (!navigator.geolocation) return null;

        this.watchId = navigator.geolocation.watchPosition(
            position => callback(position.coords),
            error => console.error('Error de geolocalización:', error),
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 30000
            }
        );

        return this.watchId;
    }

    clearWatch() {
        if (this.watchId !== null) {
            navigator.geolocation.clearWatch(this.watchId);
            this.watchId = null;
        }
    }
}

// Servicio de exportación de datos
class ExportService {

    // Exportar panoramas como JSON
    exportPanoramas() {
        const panoramas = window.dataManager.getPanoramas();
        const dataStr = JSON.stringify(panoramas, null, 2);
        this.downloadFile(dataStr, 'panoramas.json', 'application/json');
    }

    // Exportar datos CSV
    exportCsvData() {
        const csvData = window.dataManager.getCsvData();
        if (csvData.length === 0) {
            alert('No hay datos CSV para exportar');
            return;
        }

        const headers = Object.keys(csvData[0]);
        const csvContent = [
            headers.join(','),
            ...csvData.map(row =>
                headers.map(header =>
                    typeof row[header] === 'string' && row[header].includes(',')
                        ? `"${row[header]}"`
                        : row[header]
                ).join(',')
            )
        ].join('\n');

        this.downloadFile(csvContent, 'datos_exportados.csv', 'text/csv');
    }

    // Exportar estado completo
    exportFullState() {
        const state = {
            panoramas: window.dataManager.getPanoramas(),
            csvData: window.dataManager.getCsvData(),
            highlighted: Array.from(window.dataManager.getHighlighted()),
            currentPanorama: window.dataManager.getCurrentPanorama(),
            exportDate: new Date().toISOString()
        };

        const dataStr = JSON.stringify(state, null, 2);
        this.downloadFile(dataStr, 'mapa_estado_completo.json', 'application/json');
    }

    // Método auxiliar para descargar archivos
    downloadFile(content, filename, contentType) {
        const blob = new Blob([content], { type: contentType });
        const url = window.URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        window.URL.revokeObjectURL(url);

        console.log(`Archivo ${filename} descargado`);
    }
}

// Servicio de filtros y búsqueda
class FilterService {
    constructor() {
        this.filters = {
            search: '',
            highlighted: false,
            distance: null,
            center: null
        };
    }

    // Filtrar panoramas por texto
    filterBySearch(panoramas, searchText) {
        if (!searchText.trim()) return panoramas;

        const search = searchText.toLowerCase();
        return panoramas.filter(p =>
            (p.filename && p.filename.toLowerCase().includes(search)) ||
            (p.address && p.address.toLowerCase().includes(search)) ||
            p.id.toString().includes(search)
        );
    }

    // Filtrar por destacados
    filterByHighlighted(panoramas, onlyHighlighted) {
        if (!onlyHighlighted) return panoramas;

        const highlighted = window.dataManager.getHighlighted();
        return panoramas.filter(p => highlighted.has(p.id));
    }

    // Filtrar por distancia desde un punto
    filterByDistance(panoramas, center, maxDistance) {
        if (!center || !maxDistance) return panoramas;

        return panoramas.filter(p => {
            const distance = this.calculateDistance(
                center.lat, center.lng,
                p.latitude, p.longitude
            );
            return distance <= maxDistance;
        });
    }

    // Calcular distancia entre dos puntos (Haversine)
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Radio de la Tierra en km
        const dLat = this.deg2rad(lat2 - lat1);
        const dLon = this.deg2rad(lon2 - lon1);
        const a =
            Math.sin(dLat/2) * Math.sin(dLat/2) +
            Math.cos(this.deg2rad(lat1)) * Math.cos(this.deg2rad(lat2)) *
            Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    deg2rad(deg) {
        return deg * (Math.PI/180);
    }

    // Aplicar todos los filtros
    applyFilters(panoramas) {
        let filtered = panoramas;

        filtered = this.filterBySearch(filtered, this.filters.search);
        filtered = this.filterByHighlighted(filtered, this.filters.highlighted);
        filtered = this.filterByDistance(filtered, this.filters.center, this.filters.distance);

        return filtered;
    }

    // Configurar filtros
    setFilter(key, value) {
        this.filters[key] = value;
    }

    // Limpiar filtros
    clearFilters() {
        this.filters = {
            search: '',
            highlighted: false,
            distance: null,
            center: null
        };
    }
}

// Instancias globales
window.geolocationService = new GeolocationService();
window.exportService = new ExportService();
window.filterService = new FilterService();
