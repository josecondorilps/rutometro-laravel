<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Filament\Notifications\Notification;

class CampoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('filament.campo.auth.login');
        }

        /** @var User $user */
        $user = auth()->user();
        $allowedRoles = ['super_admin', 'lps_campo', 'cliente', 'user'];

        // Si el usuario no tiene rol o el rol no está autorizado
        if (!$user->role || !in_array($user->role->name, $allowedRoles)) {

            // Mostrar notificación (opcional)
            Notification::make()
                ->title('Acceso denegado')
                ->body('No tienes permisos para acceder a este panel.')
                ->danger()
                ->send();

            // Cerrar sesión
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redireccionar al login
            return redirect()->route('filament.campo.auth.login');
        }

        return $next($request);
    }
}
