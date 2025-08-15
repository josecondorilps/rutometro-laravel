/**
 * Servicio para crear y gestionar popups
 */
class PopupService {

    // Crear popup para panorama
    createPanoramaPopup(panorama) {
        const thumbnailHtml = panorama.thumbnail
            ? `<img src="${panorama.thumbnail}" alt="Vista previa" class="thumbnail" onerror="this.style.display='none'">`
            : '';

        return `
            <div class="panorama-popup">
                <div class="popup-header">
                    <h4>${panorama.filename || 'Sin nombre'}</h4>
                </div>
                <div class="popup-body">
                    <p><strong>ID:</strong> ${panorama.id}</p>
                    <p><strong>Coordenadas:</strong> ${panorama.latitude.toFixed(6)}, ${panorama.longitude.toFixed(6)}</p>
                    ${panorama.address ? `<p><strong>Dirección:</strong> ${panorama.address}</p>` : ''}
                    ${thumbnailHtml}
                    <div class="popup-actions">
                        <button class="btn-popup primary" onclick="mapController.selectPanorama(${panorama.id})">
                            Ver Panorama
                        </button>
                        <button class="btn-popup secondary" onclick="mapController.toggleHighlight(${panorama.id})">
                            ${window.dataManager.isHighlighted(panorama.id) ? 'Quitar' : 'Destacar'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Crear popup para punto CSV
    createCsvPopup(location, index) {
        let content = `
            <div class="csv-popup">
                <div class="popup-header">
                    <h4>Punto CSV #${index + 1}</h4>
                </div>
                <div class="popup-body">
                    <p><strong>Latitud:</strong> ${location.latitud?.toFixed(6)}</p>
                    <p><strong>Longitud:</strong> ${location.longitud?.toFixed(6)}</p>
        `;

        // Agregar otros campos del CSV
        Object.keys(location).forEach(key => {
            if (!['id', 'latitud', 'longitud', 'latitude', 'longitude'].includes(key) && location[key]) {
                const fieldName = key.charAt(0).toUpperCase() + key.slice(1);
                content += `<p><strong>${fieldName}:</strong> ${location[key]}</p>`;
            }
        });

        content += `
                </div>
            </div>
        `;

        return content;
    }

    // Añadir comportamiento hover a un marcador
    addHoverBehavior(marker, content) {
        let isPopupOpen = false;

        marker.on('mouseover', () => {
            if (!isPopupOpen) {
                marker.bindTooltip(this.createTooltipContent(content), {
                    permanent: false,
                    sticky: true,
                    opacity: 0.9
                }).openTooltip();
            }
        });

        marker.on('mouseout', () => {
            if (!isPopupOpen) {
                marker.closeTooltip();
            }
        });

        marker.on('popupopen', () => {
            isPopupOpen = true;
            marker.closeTooltip();
        });

        marker.on('popupclose', () => {
            isPopupOpen = false;
        });
    }

    // Crear contenido simplificado para tooltip
    createTooltipContent(fullContent) {
        // Extraer título del popup completo
        const titleMatch = fullContent.match(/<h4[^>]*>(.*?)<\/h4>/);
        return titleMatch ? titleMatch[1] : 'Información';
    }
}

// Instancia global
window.popupService = new PopupService();
