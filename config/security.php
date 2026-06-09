<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Seguridad APIDIAN
    |--------------------------------------------------------------------------
    */

    // Habilitar protección por API Key global
    'use_api_key_protection' => env('USE_PROTECTION_API_KEY', false),

    // API Key para protección global (si está habilitada)
    'api_key' => env('API_KEY', null),

    // Orígenes permitidos para CORS (separados por coma)
    // Ejemplo: 'https://miapp.com,https://otraapp.com'
    // Usar '*' para permitir todos (no recomendado en producción)
    'cors_allowed_origins' => env('CORS_ALLOWED_ORIGINS', '*'),

    // Rate limiting para API
    'rate_limit' => [
        'requests' => env('API_RATE_LIMIT', 60),
        'minutes' => env('API_RATE_LIMIT_MINUTES', 1),
    ],

    // IPs en lista blanca (bypass rate limiting)
    // Formato: '192.168.1.1,10.0.0.1'
    'ip_whitelist' => env('IP_WHITELIST', ''),

    // Días mínimos de vigencia del certificado para alertar
    'certificate_warning_days' => env('CERTIFICATE_WARNING_DAYS', 30),

    // Días mínimos de vigencia de resolución para alertar
    'resolution_warning_days' => env('RESOLUTION_WARNING_DAYS', 30),

    // Habilitar logging detallado de peticiones API
    'detailed_api_logging' => env('DETAILED_API_LOGGING', false),

    // Máximo tamaño de payload en logs (bytes) - evita logs enormes
    'max_log_payload_size' => env('MAX_LOG_PAYLOAD_SIZE', 10000),

    /*
    |--------------------------------------------------------------------------
    | Headers de Seguridad HTTP
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'SAMEORIGIN'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'hsts_enabled' => env('SECURITY_HSTS_ENABLED', false),
        'hsts_max_age' => env('SECURITY_HSTS_MAX_AGE', 31536000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Validación de Entrada
    |--------------------------------------------------------------------------
    */
    'input' => [
        // Longitud máxima de strings en requests
        'max_string_length' => env('INPUT_MAX_STRING_LENGTH', 10000),
        
        // Tamaño máximo de archivos base64 (bytes)
        'max_base64_size' => env('INPUT_MAX_BASE64_SIZE', 10485760), // 10MB
        
        // Habilitar sanitización automática de HTML
        'sanitize_html' => env('INPUT_SANITIZE_HTML', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Protección contra ataques
    |--------------------------------------------------------------------------
    */
    'protection' => [
        // Bloquear IPs después de X intentos fallidos
        'block_after_failed_attempts' => env('BLOCK_AFTER_FAILED_ATTEMPTS', 10),
        
        // Tiempo de bloqueo en minutos
        'block_duration_minutes' => env('BLOCK_DURATION_MINUTES', 30),
        
        // Habilitar detección de SQL injection en logs
        'detect_sql_injection' => env('DETECT_SQL_INJECTION', true),
    ],
];
