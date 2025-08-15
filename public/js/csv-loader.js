/**
 * Servicio para cargar y procesar archivos CSV
 */
class CsvLoader {
    constructor() {
        this.setupFileInput();
    }

    setupFileInput() {
        const fileInput = document.getElementById('csv-file');
        const loadCsvBtn = document.getElementById('load-csv');

        loadCsvBtn.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                this.loadCsvFile(file);
            }
        });
    }

    loadCsvFile(file) {
        const loading = document.getElementById('loading');
        loading.classList.remove('hidden');

        Papa.parse(file, {
            header: true,
            skipEmptyLines: true,
            dynamicTyping: true,
            transform: (value, header) => {
                // Limpiar espacios en blanco de los headers
                return typeof value === 'string' ? value.trim() : value;
            },
            complete: (results) => {
                loading.classList.add('hidden');
                this.processCsvData(results.data);
            },
            error: (error) => {
                loading.classList.add('hidden');
                console.error('Error al procesar CSV:', error);
                alert('Error al procesar el archivo CSV');
            }
        });
    }

    processCsvData(rawData) {
        console.log('Datos CSV en bruto:', rawData);

        // Filtrar filas válidas
        const validData = rawData.filter(row => {
            return this.hasValidCoordinates(row);
        });

        // Normalizar nombres de campos
        const normalizedData = validData.map((row, index) => {
            return this.normalizeRowData(row, index);
        });

        console.log(`CSV procesado: ${normalizedData.length} filas válidas de ${rawData.length} totales`);

        // Actualizar gestor de datos
        window.dataManager.setCsvData(normalizedData);

        // Notificar éxito
        this.showNotification(`CSV cargado: ${normalizedData.length} puntos`);
    }

    hasValidCoordinates(row) {
        // Buscar campos de latitud y longitud con diferentes nombres posibles
        const latFields = ['latitud', 'latitude', 'lat', 'y'];
        const lngFields = ['longitud', 'longitude', 'lng', 'lon', 'x'];

        const lat = this.findFieldValue(row, latFields);
        const lng = this.findFieldValue(row, lngFields);

        return lat !== null && lng !== null &&
            !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng)) &&
            Math.abs(parseFloat(lat)) <= 90 && Math.abs(parseFloat(lng)) <= 180;
    }

    findFieldValue(row, possibleFields) {
        for (const field of possibleFields) {
            const keys = Object.keys(row);
            const matchingKey = keys.find(key =>
                key.toLowerCase().includes(field.toLowerCase())
            );
            if (matchingKey && row[matchingKey] != null && row[matchingKey] !== '') {
                return row[matchingKey];
            }
        }
        return null;
    }

    normalizeRowData(row, index) {
        const latFields = ['latitud', 'latitude', 'lat', 'y'];
        const lngFields = ['longitud', 'longitude', 'lng', 'lon', 'x'];

        const lat = parseFloat(this.findFieldValue(row, latFields));
        const lng = parseFloat(this.findFieldValue(row, lngFields));

        return {
            id: row.id || index + 1,
            latitud: lat,
            longitud: lng,
            ...row // Mantener todos los campos originales
        };
    }

    showNotification(message) {
        // Crear notificación temporal
        const notification = document.createElement('div');
        notification.className = 'notification success';
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
        `;

        // Estilos inline para la notificación
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            animation: slideIn 0.3s ease;
        `;
    }
}
