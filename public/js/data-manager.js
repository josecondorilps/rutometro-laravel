/**
 * Gestor de datos para el mapa
 */
class DataManager {
    constructor() {
        this.panoramas = [];
        this.csvData = [];
        this.currentPanorama = null;
        this.highlightedPanoramas = new Set();
        this.subscribers = {
            panoramaChange: [],
            dataUpdate: [],
            highlightChange: []
        };
    }

    // Métodos de suscripción (patrón Observer)
    subscribe(event, callback) {
        if (this.subscribers[event]) {
            this.subscribers[event].push(callback);
        }
    }

    notify(event, data) {
        if (this.subscribers[event]) {
            this.subscribers[event].forEach(callback => callback(data));
        }
    }

    // Gestión de panoramas
    setPanoramas(panoramas) {
        this.panoramas = panoramas || [];
        this.notify('dataUpdate', { type: 'panoramas', data: this.panoramas });
        console.log(`DataManager: ${this.panoramas.length} panoramas cargados`);
    }

    getPanoramas() {
        return this.panoramas;
    }

    getPanoramaById(id) {
        return this.panoramas.find(p => p.id === id);
    }

    // Gestión de panorama actual
    setCurrentPanorama(panoramaId) {
        const previousId = this.currentPanorama;
        this.currentPanorama = panoramaId;

        if (previousId !== panoramaId) {
            this.notify('panoramaChange', {
                previous: previousId,
                current: panoramaId,
                panorama: this.getPanoramaById(panoramaId)
            });
            console.log(`DataManager: Panorama actual cambiado a ${panoramaId}`);
        }
    }

    getCurrentPanorama() {
        return this.currentPanorama;
    }

    // Gestión de datos CSV
    setCsvData(csvData) {
        this.csvData = csvData || [];
        this.notify('dataUpdate', { type: 'csv', data: this.csvData });
        console.log(`DataManager: ${this.csvData.length} puntos CSV cargados`);
    }

    getCsvData() {
        return this.csvData;
    }

    // Gestión de destacados
    addHighlighted(panoramaId) {
        this.highlightedPanoramas.add(panoramaId);
        this.notify('highlightChange', this.highlightedPanoramas);
    }

    removeHighlighted(panoramaId) {
        this.highlightedPanoramas.delete(panoramaId);
        this.notify('highlightChange', this.highlightedPanoramas);
    }

    isHighlighted(panoramaId) {
        return this.highlightedPanoramas.has(panoramaId);
    }

    getHighlighted() {
        return this.highlightedPanoramas;
    }

    // Navegación entre puntos
    getNextPanorama() {
        if (!this.currentPanorama || this.panoramas.length === 0) return null;

        const currentIndex = this.panoramas.findIndex(p => p.id === this.currentPanorama);
        const nextIndex = (currentIndex + 1) % this.panoramas.length;
        return this.panoramas[nextIndex];
    }

    getPreviousPanorama() {
        if (!this.currentPanorama || this.panoramas.length === 0) return null;

        const currentIndex = this.panoramas.findIndex(p => p.id === this.currentPanorama);
        const prevIndex = currentIndex === 0 ? this.panoramas.length - 1 : currentIndex - 1;
        return this.panoramas[prevIndex];
    }

    getCurrentPosition() {
        if (!this.currentPanorama || this.panoramas.length === 0) return { current: 0, total: 0 };

        const currentIndex = this.panoramas.findIndex(p => p.id === this.currentPanorama);
        return {
            current: currentIndex + 1,
            total: this.panoramas.length
        };
    }
}

// Instancia global
window.dataManager = new DataManager();
