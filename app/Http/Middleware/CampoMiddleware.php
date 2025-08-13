<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CampoMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('filament.campo.auth.login');
        }

        /** @var User $user */
        $user = auth()->user();

        $allowedRoles = ['super_admin', 'lps_campo', 'cliente', 'user'];

        if (!in_array($user->role?->name, $allowedRoles)) {
            abort(403, 'No tienes permisos para acceder a este panel.');
        }

        return $next($request);
    }
}
