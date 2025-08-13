<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CampoPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('campo')
            ->path('/campo')
            ->brandName('LPS Grupo - Campo')
            ->login()
            ->colors([
                'primary' => [
                    50 => '#eff8ff',
                    100 => '#dbeafe',
                    200 => '#bfdbfe',
                    300 => '#93c5fd',
                    400 => '#60a5fa',
                    500 => '#1e40af',
                    600 => '#1d4ed8',
                    700 => '#1e3a8a',
                    800 => '#172554',
                    900 => '#0f172a',
                    950 => '#020617',
                ],
                'danger' => Color::Red,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->discoverResources(
                in: app_path('Filament/Campo/Resources'),
                for: 'App\\Filament\\Campo\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Campo/Pages'),
                for: 'App\\Filament\\Campo\\Pages'
            )
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Campo/Widgets'),
                for: 'App\\Filament\\Campo\\Widgets'
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                \App\Http\Middleware\CampoMiddleware::class,
            ])
            ->renderHook('panels::head.end', fn () => <<<HTML
                    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
                          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
                          crossorigin="" />
                    <!-- Leaflet MarkerCluster CSS -->
                    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css" />
                    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css" />
                    <style>
                        /* Estilos personalizados para mapas LPS Grupo */
                        .leaflet-container {
                            border-radius: 0.5rem;
                            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                        }
                        .leaflet-popup-content-wrapper {
                            border-radius: 0.5rem;
                            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
                        }
                        .leaflet-popup-tip {
                            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
                        }
                        .custom-marker {
                            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
                        }
                        /* Estilos para clusters */
                        .marker-cluster-small {
                            background-color: rgba(59, 130, 246, 0.6);
                        }
                        .marker-cluster-small div {
                            background-color: rgba(59, 130, 246, 0.8);
                        }
                        .marker-cluster-medium {
                            background-color: rgba(251, 191, 36, 0.6);
                        }
                        .marker-cluster-medium div {
                            background-color: rgba(251, 191, 36, 0.8);
                        }
                        .marker-cluster-large {
                            background-color: rgba(239, 68, 68, 0.6);
                        }
                        .marker-cluster-large div {
                            background-color: rgba(239, 68, 68, 0.8);
                        }
                    </style>
                HTML)
            // ✅ Cargar Leaflet JS antes del cierre del body
            ->renderHook('panels::body.end', fn () => <<<'HTML'
                <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
                        crossorigin=""></script>
                <!-- Leaflet MarkerCluster JS -->
                <script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
                <script>
                    // Configuración global de Leaflet para LPS Grupo
                    if (typeof L !== 'undefined') {
                        console.log('✅ Leaflet + MarkerCluster cargado correctamente para LPS Grupo');

                        // Configuración por defecto
                        L.Icon.Default.imagePath = 'https://unpkg.com/leaflet@1.9.4/dist/images/';

                        // Función global para validar coordenadas
                        window.validateCoordinates = function(lat, lng) {
                            return lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
                        };

                        // Función global para formatear coordenadas
                        window.formatCoordinates = function(lat, lng, decimals = 6) {
                            return `${parseFloat(lat).toFixed(decimals)}, ${parseFloat(lng).toFixed(decimals)}`;
                        };

                        // Función global para calcular distancia entre puntos
                        window.calculateDistance = function(lat1, lng1, lat2, lng2) {
                            const R = 6371; // Radio de la Tierra en km
                            const dLat = (lat2 - lat1) * Math.PI / 180;
                            const dLng = (lng2 - lng1) * Math.PI / 180;
                            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                                    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                                    Math.sin(dLng/2) * Math.sin(dLng/2);
                            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                            return R * c;
                        };
                    } else {
                        console.error('❌ Error: Leaflet no se pudo cargar');
                    }
                </script>
            HTML);
    }
}
