<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Mendukung Spatie HasRoles dan juga fallback ke kolom 'role'
        $hasRole = false;
        if (method_exists($user, 'hasRole')) {
            $hasRole = $user->hasRole($role);
        } elseif (isset($user->role)) {
            $hasRole = $user->role === $role;
        }

        if (!$hasRole) {
            return response()->json(['message' => 'Akses ditolak. Role tidak sesuai.'], 403);
        }

        return $next($request);
    }
}

