<?php

namespace App\Http\Middleware;

use Closure;
use App\Log;

class ApiLog
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Sanitizar payload para no guardar datos sensibles
        $payload = $this->sanitizePayload($request->toArray());
        
        // Limitar tamaño del payload en logs
        $maxSize = config('security.max_log_payload_size', 10000);
        $payloadJson = json_encode($payload);
        if (strlen($payloadJson) > $maxSize) {
            $payload = ['_truncated' => true, '_size' => strlen($payloadJson)];
        }

        Log::create([
            'payload' => [
                'body' => $payload,
                'uri' => $request->getRequestUri(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ],
            'user_id' => optional(auth()->user())->id,
        ]);

        return $next($request);
    }

    /**
     * Sanitizar payload para remover datos sensibles
     *
     * @param array $payload
     * @return array
     */
    private function sanitizePayload(array $payload): array
    {
        $sensitiveFields = [
            'password', 'certificate', 'pin', 'api_token', 'token',
            'mail_password', 'imap_password', 'secret'
        ];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->sanitizePayload($value);
            } elseif (in_array(strtolower($key), $sensitiveFields)) {
                $payload[$key] = '***REDACTED***';
            }
        }

        return $payload;
    }
}
