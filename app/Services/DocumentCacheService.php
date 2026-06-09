<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\Document;

/**
 * Servicio de cache para documentos
 * 
 * Cachea consultas frecuentes de documentos para mejorar rendimiento
 * en validaciones de duplicados y consultas de estado.
 */
class DocumentCacheService
{
    /**
     * TTL corto para documentos (5 minutos)
     * Los documentos cambian frecuentemente
     */
    const CACHE_TTL = 300;

    /**
     * Prefijo para claves de cache
     */
    const CACHE_PREFIX = 'doc_';

    /**
     * Verificar si un documento ya existe (para evitar duplicados)
     * 
     * @param string $identificationNumber NIT de la empresa
     * @param string $prefix Prefijo de la resolución
     * @param int $number Número del documento
     * @param int $typeDocumentId Tipo de documento
     * @return Document|null
     */
    public static function findExistingDocument(
        string $identificationNumber,
        string $prefix,
        int $number,
        int $typeDocumentId
    ): ?Document {
        $cacheKey = self::CACHE_PREFIX . "exists_{$identificationNumber}_{$prefix}_{$number}_{$typeDocumentId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($identificationNumber, $prefix, $number, $typeDocumentId) {
            return Document::where('identification_number', $identificationNumber)
                ->where('prefix', $prefix)
                ->where('number', $number)
                ->where('type_document_id', $typeDocumentId)
                ->where('state_document_id', 1) // Solo documentos válidos
                ->first();
        });
    }

    /**
     * Obtener el último número de documento para una resolución
     * 
     * @param string $identificationNumber
     * @param string $prefix
     * @param int $typeDocumentId
     * @return int|null
     */
    public static function getLastDocumentNumber(
        string $identificationNumber,
        string $prefix,
        int $typeDocumentId
    ): ?int {
        $cacheKey = self::CACHE_PREFIX . "last_{$identificationNumber}_{$prefix}_{$typeDocumentId}";
        
        // Cache más corto para números (30 segundos)
        return Cache::remember($cacheKey, 30, function () use ($identificationNumber, $prefix, $typeDocumentId) {
            return Document::where('identification_number', $identificationNumber)
                ->where('prefix', $prefix)
                ->where('type_document_id', $typeDocumentId)
                ->where('state_document_id', 1)
                ->max('number');
        });
    }

    /**
     * Invalidar cache de documento específico
     */
    public static function invalidateDocument(
        string $identificationNumber,
        string $prefix,
        int $number,
        int $typeDocumentId
    ): void {
        $cacheKey = self::CACHE_PREFIX . "exists_{$identificationNumber}_{$prefix}_{$number}_{$typeDocumentId}";
        Cache::forget($cacheKey);
        
        // También invalidar el último número
        $lastKey = self::CACHE_PREFIX . "last_{$identificationNumber}_{$prefix}_{$typeDocumentId}";
        Cache::forget($lastKey);
    }

    /**
     * Invalidar todo el cache de documentos de una empresa
     */
    public static function invalidateCompanyDocuments(string $identificationNumber): void
    {
        // Con Redis podemos usar patrones
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $prefix = config('cache.prefix') . ':' . self::CACHE_PREFIX;
                $pattern = $prefix . "*_{$identificationNumber}_*";
                $keys = $redis->keys($pattern);
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                // Ignorar errores de Redis
            }
        }
    }
}
