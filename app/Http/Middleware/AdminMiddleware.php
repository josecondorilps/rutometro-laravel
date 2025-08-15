<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        /** @var User $user */
        $user = auth()->user();
        $allowedRoles = ['super_admin', 'lps_admin'];

        // Si el usuario no tiene rol o el rol no está autorizado
        if (!$user->role || !in_array($user->role->name, $allowedRoles)) {
            // Cerrar sesión del usuario
            Auth::logout();

            // Invalidar la sesión
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Redireccionar al login con mensaje de error
            return redirect()
                ->route('filament.admin.auth.login')
                ->withErrors([
                    'email' => 'No tienes permisos para acceder al panel de administración.'
                ]);
        }

        return $next($request);
    }
}
