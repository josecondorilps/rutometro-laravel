<?php

namespace App\Filament\Campo\Widgets;

use Filament\Widgets\Widget;
use Livewire\Component;

class MapaEquiposWidget extends Widget
{
    protected  string $view = 'filament.campo.widgets.mapa-equipos-widget';

    // Variables que faltan - agregar estas propiedades
    public bool $mostrarRuta = false;
    public bool $mostrarCobertura = false;
    public string $filtroEstado = 'todos';

    // Otras propiedades que probablemente ya tienes
    public $equipos = [];
    public $totalEquipos = [];

    public function mount()
    {
        // Inicializar datos si es necesario
        $this->loadEquipos();
        $this->calculateTotals();
    }

    // Método para alternar mostrar/ocultar ruta
    public function toggleMostrarRuta()
    {
        $this->mostrarRuta = !$this->mostrarRuta;

        // Opcional: Dispatchar evento para JavaScript
        $this->dispatch('toggleRuta', ['mostrar' => $this->mostrarRuta]);
    }

    // Método para alternar mostrar/ocultar cobertura
    public function toggleMostrarCobertura()
    {
        $this->mostrarCobertura = !$this->mostrarCobertura;

        // Dispatchar evento para JavaScript
        $this->dispatch('toggleCirculosCobertura', ['mostrar' => $this->mostrarCobertura]);
    }

    // Método para filtrar por estado
    public function filtrarPorEstado($estado)
    {
        $this->filtroEstado = $estado;
        $this->loadEquipos(); // Recargar equipos con filtro
    }

    // Método para resetear filtros
    public function resetearFiltros()
    {
        $this->filtroEstado = 'todos';
        $this->mostrarRuta = false;
        $this->mostrarCobertura = false;
        $this->loadEquipos();

        // Dispatchar eventos para JavaScript
        $this->dispatch('resetearMapa');
    }

    // Método para centrar en equipo (si quieres mantener comunicación con Livewire)
    public function centrarEnEquipo($equipoId)
    {
        // Buscar el equipo
        $equipo = collect($this->equipos)->firstWhere('id', $equipoId);

        if ($equipo && isset($equipo['latitud']) && isset($equipo['longitud'])) {
            $this->dispatch('centrarEnPunto', [
                'lat' => (float) $equipo['latitud'],
                'lng' => (float) $equipo['longitud'],
                'zoom' => 16
            ]);
        }
    }

    private function loadEquipos()
    {
        // Tu lógica para cargar equipos
        // Ejemplo básico - ajusta según tu modelo

        $query = \App\Models\Equipo::with(['ruta', 'inspector'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud');

        // Aplicar filtro si no es 'todos'
        if ($this->filtroEstado !== 'todos') {
            switch ($this->filtroEstado) {
                case 'inspeccionados':
                    $query->whereNotNull('fecha_inspeccion');
                    break;
                case 'pendientes':
                    $query->whereNull('fecha_inspeccion');
                    break;
                default:
                    $query->where('estado', $this->filtroEstado);
                    break;
            }
        }

        $this->equipos = $query->get()->toArray();
        $this->calculateTotals();
    }

    private function calculateTotals()
    {
        $equipos = collect($this->equipos);

        $this->totalEquipos = [
            'total' => $equipos->count(),
            'operativos' => $equipos->where('estado', 'operativo')->count(),
            'mantenimiento' => $equipos->where('estado', 'mantenimiento')->count(),
            'fuera_servicio' => $equipos->where('estado', 'fuera_servicio')->count(),
            'inspeccionados' => $equipos->whereNotNull('fecha_inspeccion')->count(),
            'pendientes' => $equipos->whereNull('fecha_inspeccion')->count(),
        ];
    }

    // Actualizar JavaScript cuando cambien las propiedades
    public function updatedMostrarRuta()
    {
        $this->dispatch('actualizarMapa');
    }

    public function updatedMostrarCobertura()
    {
        $this->dispatch('actualizarMapa');
    }

    public function updatedFiltroEstado()
    {
        $this->loadEquipos();
    }

    // Método para obtener el ID único del widget
    public function getId()
    {
        return $this->id ?? 'widget-' . uniqid();
    }
}
