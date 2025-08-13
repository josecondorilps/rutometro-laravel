<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\CsvImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class CsvImportService
{
    protected $csvImportRecord;
    protected $stats = [
        'total_rows' => 0,
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => 0,
        'skipped' => 0
    ];

    public function processCsv(string $filePath, int $userId, ?int $proyectoId = null): array
    {
        // Crear registro de tracking
        $this->csvImportRecord = CsvImport::create([
            'file_path' => $filePath,
            'proyecto_id' => $proyectoId,
            'user_id' => $userId,
            'status' => 'processing',
            'started_at' => now()
        ]);

        try {
            $this->processFile($filePath);

            $this->csvImportRecord->update([
                'status' => 'completed',
                'completed_at' => now(),
                'stats' => $this->stats
            ]);

        } catch (Exception $e) {
            $this->csvImportRecord->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
                'stats' => $this->stats
            ]);
            throw $e;
        }

        return $this->stats;
    }

    protected function removeBOM(string $text): string
    {
        // Remover UTF-8 BOM
        if (substr($text, 0, 3) == "\xEF\xBB\xBF") {
            return substr($text, 3);
        }

        // Remover otros BOMs comunes
        if (substr($text, 0, 2) == "\xFF\xFE" || substr($text, 0, 2) == "\xFE\xFF") {
            return substr($text, 2);
        }

        return $text;
    }
    protected function processFile(string $filePath): void
    {
        $fullPath = storage_path('app/public/' . $filePath);

        if (!file_exists($fullPath)) {
            throw new Exception("Archivo CSV no encontrado: {$fullPath}");
        }

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            throw new Exception("No se pudo abrir el archivo CSV");
        }

        // Leer headers con punto y coma
        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            throw new Exception("CSV vacío o sin headers");
        }

        if (!empty($headers[0])) {
            $headers[0] = $this->removeBOM($headers[0]);
        }

        // DEBUG: Agregar logging para ver headers
        Log::info('Headers CSV encontrados:', $headers);

        $headerMap = $this->mapHeaders($headers);

        // DEBUG: Ver mapeo
        Log::info('Mapeo de headers:', $headerMap);

        $this->validateRequiredHeaders($headerMap);

        $batchSize = 100;
        $batch = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            $rowNumber++;
            $this->stats['total_rows']++;

            if (count($row) !== count($headers)) {
                $this->logError($rowNumber, "Número incorrecto de columnas");
                continue;
            }

            $equipoData = $this->mapRowData($row, $headers, $headerMap);

            if ($equipoData) {
                $batch[] = $equipoData;

                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch);
                    $batch = [];
                }
            }
        }

        // Procesar último lote
        if (!empty($batch)) {
            $this->processBatch($batch);
        }

        fclose($handle);
    }

    protected function mapHeaders(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $cleanHeader = strtolower(trim($header));

            $map[$index] = match ($cleanHeader) {
                // Identificador - más variantes
                'identification', 'id', 'identificador', 'equipo_id', 'equipment_id', 'codigo', 'code' => 'identificador',

                // Latitud - más variantes
                'lat', 'latitude', 'latitud', 'y', 'coord_y', 'coordenada_y' => 'latitud',

                // Longitud - más variantes
                'long', 'longitude', 'longitud', 'lng', 'x', 'coord_x', 'coordenada_x' => 'longitud',

                // Dirección
                'address', 'direccion', 'ubicacion', 'location' => 'direccion',

                // Observaciones
                'remarks', 'observaciones', 'notas', 'notes', 'comentarios' => 'observaciones_campo',

                // Área
                'area', 'zona', 'zone', 'sector' => 'area',

                // Estado
                'usage state', 'estado', 'state', 'status', 'condition' => 'estado',

                // Tipo
                'type', 'tipo', 'equipment_type', 'categoria' => 'tipo',

                // Altitud
                'altitude', 'altitud', 'elevation', 'z' => 'altitud',

                // Dirección geocodificada
                'geocoded_address' => 'direccion_geocodificada',

                default => null
            };
        }

        return $map;
    }

    protected function validateRequiredHeaders(array $headerMap): void
    {
        $required = ['identificador', 'latitud', 'longitud'];
        $found = array_filter($headerMap);
        $missing = [];

        foreach ($required as $field) {
            if (!in_array($field, $found)) {
                $missing[] = $field;
            }
        }

        if (!empty($missing)) {
            $foundFields = implode(', ', $found);
            $missingFields = implode(', ', $missing);
            $headersFound = implode(', ', array_keys($headerMap));

            throw new Exception("Campos requeridos no encontrados: [{$missingFields}]. Headers del CSV: [{$headersFound}]. Campos mapeados: [{$foundFields}]");
        }
    }

    protected function mapRowData(array $row, array $headers, array $headerMap): ?array
    {
        $data = [];

        foreach ($row as $index => $value) {
            $field = $headerMap[$index] ?? null;
            if ($field && !empty(trim($value))) {
                $data[$field] = trim($value);
            }
        }

        // Validar datos mínimos
        if (empty($data['identificador']) ||
            empty($data['latitud']) ||
            empty($data['longitud'])) {
            $this->stats['skipped']++;
            return null;
        }

        // Convertir coordenadas (manejar comas decimales)
        $data['latitud'] = $this->parseCoordinate($data['latitud']);
        $data['longitud'] = $this->parseCoordinate($data['longitud']);

        if (isset($data['altitud'])) {
            $data['altitud'] = $this->parseCoordinate($data['altitud']);
        }

        // Validar que las coordenadas son números válidos
        if ($data['latitud'] == 0 || $data['longitud'] == 0) {
            $this->logError(0, "Coordenadas inválidas para: {$data['identificador']}");
            $this->stats['skipped']++;
            return null;
        }

        // Validar rangos de coordenadas (Perú)
        /*
        if ($data['latitud'] < -18.5 || $data['latitud'] > -0.5 ||
            $data['longitud'] < -81.5 || $data['longitud'] > -68.5) {
            $this->logError(0, "Coordenadas fuera de rango válido para Perú: {$data['identificador']}");
            $this->stats['skipped']++;
            return null;
        }
        */

        return $data;
    }

    // NUEVO: Método para parsear coordenadas con comas
    protected function parseCoordinate($value): float
    {
        if (empty($value)) return 0.0;

        // Convertir coma decimal a punto
        $cleaned = str_replace(',', '.', $value);

        // Remover espacios y caracteres no numéricos (excepto punto y signo negativo)
        $cleaned = preg_replace('/[^-0-9.]/', '', $cleaned);

        return (float) $cleaned;
    }

    protected function processBatch(array $batch): void
    {
        Log::info("=== INICIANDO INSERCIÓN MASIVA ===");
        Log::info("Registros en lote: " . count($batch));

        try {
            DB::transaction(function () use ($batch) {
                $insertData = [];
                $updateData = [];

                foreach ($batch as $equipoData) {
                    // Preparar datos para inserción directa
                    $cleanData = [
                        'identificador' => $equipoData['identificador'],
                        'latitud' => $equipoData['latitud'],
                        'longitud' => $equipoData['longitud'],
                        'direccion' => $equipoData['direccion'] ?? null,
                        'tipo' => $equipoData['tipo'] ?? null,
                        'area' => $equipoData['area'] ?? null,
                        'estado' => $equipoData['estado'] ?? null,
                        'observaciones_campo' => $equipoData['observaciones_campo'] ?? null,
                        'altitud' => $equipoData['altitud'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Verificar si ya existe
                    $exists = Equipo::where('identificador', $equipoData['identificador'])->exists();

                    if ($exists) {
                        $updateData[] = $cleanData;
                        $this->stats['updated']++;
                    } else {
                        $insertData[] = $cleanData;
                        $this->stats['created']++;
                    }

                    $this->stats['processed']++;
                }

                // INSERCIÓN MASIVA
                if (!empty($insertData)) {
                    Log::info("Insertando " . count($insertData) . " equipos nuevos");
                    Equipo::insert($insertData);
                }

                // ACTUALIZACIÓN INDIVIDUAL (porque no hay update masivo)
                if (!empty($updateData)) {
                    Log::info("Actualizando " . count($updateData) . " equipos existentes");
                    foreach ($updateData as $data) {
                        Equipo::where('identificador', $data['identificador'])->update($data);
                    }
                }
            });

            Log::info("=== LOTE COMPLETADO ===");
            Log::info("Stats: " . json_encode($this->stats));

        } catch (Exception $e) {
            Log::error("ERROR EN INSERCIÓN MASIVA: " . $e->getMessage());
            throw $e;
        }
    }

    protected function logError(int $row, string $message): void
    {
        Log::warning("CSV Import Error", [
            'row' => $row,
            'message' => $message,
            'csv_import_id' => $this->csvImportRecord->id ?? null
        ]);

        $this->stats['errors']++;
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}
