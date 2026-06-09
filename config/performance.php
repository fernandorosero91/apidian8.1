<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Rendimiento APIDIAN
    |--------------------------------------------------------------------------
    |
    | Configuraciones para optimizar el rendimiento de la API
    |
    */

    // Cache de catálogos
    'catalog_cache' => [
        'enabled' => env('CATALOG_CACHE_ENABLED', true),
        'ttl' => env('CATALOG_CACHE_TTL', 86400), // 24 horas
    ],

    // Verificación de estado DIAN
    'dian_check' => [
        'cache_ttl' => env('DIAN_CHECK_CACHE_TTL', 30), // segundos
        'timeout' => env('DIAN_CHECK_TIMEOUT', 5), // segundos
        'connect_timeout' => env('DIAN_CONNECT_TIMEOUT', 3), // segundos
    ],

    // Generación de PDFs
    'pdf' => [
        'memory_limit' => env('PDF_MEMORY_LIMIT', '512M'),
        'time_limit' => env('PDF_TIME_LIMIT', 120), // segundos
        'temp_dir' => env('PDF_TEMP_DIR', null), // null = default
    ],

    // Procesamiento de XML
    'xml' => [
        'memory_limit' => env('XML_MEMORY_LIMIT', '256M'),
    ],

    // Conexiones HTTP
    'http' => [
        'timeout' => env('HTTP_TIMEOUT', 30),
        'connect_timeout' => env('HTTP_CONNECT_TIMEOUT', 10),
        'retry_attempts' => env('HTTP_RETRY_ATTEMPTS', 2),
    ],

    // Queries de base de datos
    'database' => [
        'chunk_size' => env('DB_CHUNK_SIZE', 1000),
        'slow_query_log' => env('DB_SLOW_QUERY_LOG', false),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000), // ms
    ],
];
