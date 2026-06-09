<?php

namespace App\Services;

/**
 * Servicio para sanitizar y validar entradas de usuario
 * 
 * Proporciona métodos para limpiar datos de entrada y prevenir
 * ataques de inyección.
 */
class InputSanitizer
{
    /**
     * Tablas permitidas para consultas dinámicas
     * Solo estas tablas pueden ser consultadas vía API
     */
    const ALLOWED_TABLES = [
        'type_documents',
        'type_document_identifications',
        'type_organizations',
        'type_regimes',
        'type_liabilities',
        'type_operations',
        'type_currencies',
        'type_environments',
        'municipalities',
        'departments',
        'countries',
        'taxes',
        'payment_forms',
        'payment_methods',
        'unit_measures',
        'languages',
        'discounts',
        'reference_prices',
        'type_item_identifications',
        'events',
        'type_rejections',
        'incoterms',
        'type_contracts',
        'type_workers',
        'sub_type_workers',
        'payroll_periods',
        'health_type_document_identifications',
        'health_type_users',
        'health_coverages',
        'health_contracting_payment_methods',
        'health_type_operations',
        'credit_note_discrepancy_responses',
        'debit_note_discrepancy_responses',
        'prepaid_payment_types',
    ];

    /**
     * Columnas sensibles que no deben ser expuestas
     */
    const SENSITIVE_COLUMNS = [
        'password',
        'api_token',
        'remember_token',
        'certificate',
        'pin',
        'secret',
    ];

    /**
     * Verificar si una tabla está permitida para consultas
     */
    public static function isTableAllowed(string $tableName): bool
    {
        return in_array(strtolower($tableName), self::ALLOWED_TABLES);
    }

    /**
     * Verificar si una columna es sensible
     */
    public static function isSensitiveColumn(string $columnName): bool
    {
        return in_array(strtolower($columnName), self::SENSITIVE_COLUMNS);
    }

    /**
     * Sanitizar nombre de tabla (solo alfanumérico y guiones bajos)
     */
    public static function sanitizeTableName(string $tableName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $tableName);
    }

    /**
     * Sanitizar nombre de columna
     */
    public static function sanitizeColumnName(string $columnName): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $columnName);
    }

    /**
     * Sanitizar valor de búsqueda
     */
    public static function sanitizeSearchValue(string $value): string
    {
        // Remover caracteres peligrosos para SQL
        $value = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $value);
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar NIT (solo números)
     */
    public static function sanitizeNit(string $nit): string
    {
        return preg_replace('/[^0-9]/', '', $nit);
    }

    /**
     * Validar formato de email
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Sanitizar string para uso en XML
     */
    public static function sanitizeForXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Remover tags HTML/PHP de un string
     */
    public static function stripTags(string $value): string
    {
        return strip_tags($value);
    }

    /**
     * Validar y sanitizar base64
     */
    public static function isValidBase64(string $data): bool
    {
        if (empty($data)) {
            return false;
        }
        
        $decoded = base64_decode($data, true);
        return $decoded !== false && base64_encode($decoded) === $data;
    }
}
