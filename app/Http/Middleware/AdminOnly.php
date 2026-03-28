<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Vérifier si l'utilisateur est admin
        if (!$user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Admin access required.'], 403);
            }

            // Log l'accès refusé pour audit
            \Log::warning('Admin Access Denied', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role,
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
            ]);

            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
