<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('filament.admin.auth.login');
        }
        /** @var User $user */
        $user = auth()->user();

        $allowedRoles = ['super_admin', 'lps_admin'];

        if (!$user->role || !in_array($user->role->name, $allowedRoles)) {
            abort(403, 'Acceso denegado. Solo administradores.');
        }

        return $next($request);
    }
}
