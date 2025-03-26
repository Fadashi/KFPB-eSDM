<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Debug log
        Log::info('Middleware CheckRole', [
            'User Role' => $user?->role ?? 'Guest',
            'Allowed Roles' => $roles
        ]);

        // Jika middleware dipanggil tanpa parameter role, tampilkan error
        if (empty($roles) || (count($roles) == 1 && empty($roles[0]))) {
            Log::error('Middleware CheckRole dipanggil tanpa role yang valid.');
            abort(500, 'Middleware CheckRole tidak memiliki role yang valid.');
        }

        // Jika user tidak ada atau rolenya tidak sesuai
        if (!$user || !in_array($user->role, $roles)) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
