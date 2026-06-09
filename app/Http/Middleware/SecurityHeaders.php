<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Middleware para agregar headers de seguridad HTTP
 * 
 * Protege contra ataques comunes como XSS, clickjacking, etc.
 */
class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Prevenir clickjacking
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Prevenir MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Habilitar protección XSS del navegador
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy (antes Feature-Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // En producción, agregar HSTS
        if (config('app.env') === 'production' && config('app.force_https', false)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
