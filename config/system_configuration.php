<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración del Sistema APIDIAN
    |--------------------------------------------------------------------------
    */

    // Permitir descarga pública de documentos (XML, PDF)
    'allow_public_download' => env('ALLOW_PUBLIC_DOWNLOAD', true),

    // Forzar HTTPS en todas las URLs generadas
    'force_https' => env('FORCE_HTTPS', false),

    // Validar si el documento ya existe antes de enviarlo a la DIAN
    // Recomendado: true en producción para evitar duplicados
    'validate_before_sending' => env('VALIDATE_BEFORE_SENDING', true),

    // Enviar credenciales automáticamente a nuevos clientes
    'apply_send_customer_credentials' => env('APPLY_SEND_CUSTOMER_CREDENTIALS', true),

    // Guardar respuesta completa de la DIAN en la base de datos
    // Útil para auditoría y debugging
    'save_response_dian_to_db' => env('SAVE_RESPONSE_DIAN_TO_DB', false),

    // Habilitar registro de empresas vía API
    // Recomendado: false en producción si no es necesario
    'enable_api_register' => env('ENABLE_API_REGISTER', true),

    // Tiempo máximo de espera para conexión con DIAN (segundos)
    'dian_connection_timeout' => env('DIAN_CONNECTION_TIMEOUT', 30),

    // Reintentos automáticos en caso de fallo de conexión con DIAN
    'dian_retry_attempts' => env('DIAN_RETRY_ATTEMPTS', 3),
];
