<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Equipo;
use Illuminate\Support\Facades\Log;

class ProcessCsvImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $csvPath;
    protected $proyectoId;
    protected $userId;

    public function __construct(string $csvPath, int $proyectoId, int $userId)
    {
        $this->csvPath = $csvPath;
        $this->proyectoId = $proyectoId;
        $this->userId = $userId;
    }

    public function handle(): void
    {
        Log::info("Iniciando procesamiento CSV: {$this->csvPath}");

        $equiposCreados = 0;
        $equiposActualizados = 0;

        if (($handle = fopen(storage_path('app/public/' . $this->csvPath), 'r')) !== FALSE) {
            $headers = fgetcsv($handle); // Primera fila con headers
            $headerMap = $this->mapearHeaders($headers);

            while (($row = fgetcsv($handle)) !== FALSE) {
                if (count($row) !== count($headers)) continue;

                $equipoData = array_combine($headers, $row);
                $result = $this->procesarEquipo($equipoData, $headerMap);

                if ($result === 'creado') {
                    $equiposCreados++;
                } elseif ($result === 'actualizado') {
                    $equiposActualizados++;
                }
            }

            fclose($handle);
        }

        Log::info("CSV procesado: {$equiposCreados} creados, {$equiposActualizados} actualizados");
    }

    protected function mapearHeaders(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $map[$index] = match (strtolower(trim($header))) {
                'identification', 'id', 'identificador' => 'identificador',
                'lat', 'latitude', 'latitud' => 'latitud',
                'long', 'longitude', 'longitud' => 'longitud',
                'address', 'direccion' => 'direccion',
                'remarks', 'observaciones' => 'observaciones',
                'area', 'zona' => 'area',
                'usage state', 'estado', 'state' => 'estado',
                'tipo', 'type' => 'tipo',
                'altitud', 'altitude' => 'altitud',
                default => null
            };
        }

        return $map;
    }

    protected function procesarEquipo(array $data, array $headerMap): string
    {
        $equipoData = [];

        foreach ($data as $index => $valor) {
            $campo = $headerMap[$index] ?? null;
            if ($campo && !empty(trim($valor))) {
                $equipoData[$campo] = trim($valor);
            }
        }

        if (empty($equipoData['identificador']) ||
            empty($equipoData['latitud']) ||
            empty($equipoData['longitud'])) {
            return 'omitido';
        }

        $equipoData['latitud'] = (float) $equipoData['latitud'];
        $equipoData['longitud'] = (float) $equipoData['longitud'];
        $equipoData['proyecto_id'] = $this->proyectoId;

        $equipo = Equipo::updateOrCreate(
            ['identificador' => $equipoData['identificador']],
            $equipoData
        );

        return $equipo->wasRecentlyCreated ? 'creado' : 'actualizado';
    }
}
