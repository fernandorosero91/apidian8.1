<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use App\TypeDocument;
use App\TypeOperation;
use App\PaymentForm;
use App\PaymentMethod;
use App\Municipality;
use App\TypeRegime;
use App\TypeLiability;
use App\TypeOrganization;
use App\TypeDocumentIdentification;
use App\Tax;
use App\TypeCurrency;
use App\UnitMeasure;

/**
 * Servicio de cache para catálogos DIAN
 * 
 * Cachea las tablas de catálogos que raramente cambian para evitar
 * consultas repetitivas a la base de datos en cada petición.
 * 
 * SEGURO: Solo lectura de datos, no modifica funcionalidad existente.
 */
class CatalogCacheService
{
    /**
     * Tiempo de cache en segundos (24 horas)
     */
    const CACHE_TTL = 86400;

    /**
     * Prefijo para las claves de cache
     */
    const CACHE_PREFIX = 'catalog_';

    /**
     * Obtener TypeDocument por ID (cacheado)
     */
    public static function getTypeDocument(int $id): ?TypeDocument
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_document_{$id}",
            self::CACHE_TTL,
            fn() => TypeDocument::find($id)
        );
    }

    /**
     * Obtener TypeOperation por ID (cacheado)
     */
    public static function getTypeOperation(int $id): ?TypeOperation
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_operation_{$id}",
            self::CACHE_TTL,
            fn() => TypeOperation::find($id)
        );
    }

    /**
     * Obtener PaymentForm por ID (cacheado)
     */
    public static function getPaymentForm(int $id): ?PaymentForm
    {
        return Cache::remember(
            self::CACHE_PREFIX . "payment_form_{$id}",
            self::CACHE_TTL,
            fn() => PaymentForm::find($id)
        );
    }

    /**
     * Obtener PaymentMethod por ID (cacheado)
     */
    public static function getPaymentMethod(int $id): ?PaymentMethod
    {
        return Cache::remember(
            self::CACHE_PREFIX . "payment_method_{$id}",
            self::CACHE_TTL,
            fn() => PaymentMethod::find($id)
        );
    }

    /**
     * Obtener Municipality por codefacturador (cacheado)
     */
    public static function getMunicipalityByCode(string $code): ?Municipality
    {
        return Cache::remember(
            self::CACHE_PREFIX . "municipality_code_{$code}",
            self::CACHE_TTL,
            fn() => Municipality::where('codefacturador', $code)->first()
        );
    }

    /**
     * Obtener Municipality por ID (cacheado)
     */
    public static function getMunicipality(int $id): ?Municipality
    {
        return Cache::remember(
            self::CACHE_PREFIX . "municipality_{$id}",
            self::CACHE_TTL,
            fn() => Municipality::find($id)
        );
    }

    /**
     * Obtener TypeRegime por ID (cacheado)
     */
    public static function getTypeRegime(int $id): ?TypeRegime
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_regime_{$id}",
            self::CACHE_TTL,
            fn() => TypeRegime::find($id)
        );
    }

    /**
     * Obtener TypeLiability por ID (cacheado)
     */
    public static function getTypeLiability(int $id): ?TypeLiability
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_liability_{$id}",
            self::CACHE_TTL,
            fn() => TypeLiability::find($id)
        );
    }

    /**
     * Obtener TypeOrganization por ID (cacheado)
     */
    public static function getTypeOrganization(int $id): ?TypeOrganization
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_organization_{$id}",
            self::CACHE_TTL,
            fn() => TypeOrganization::find($id)
        );
    }

    /**
     * Obtener TypeDocumentIdentification por ID (cacheado)
     */
    public static function getTypeDocumentIdentification(int $id): ?TypeDocumentIdentification
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_doc_identification_{$id}",
            self::CACHE_TTL,
            fn() => TypeDocumentIdentification::find($id)
        );
    }

    /**
     * Obtener Tax por ID (cacheado)
     */
    public static function getTax(int $id): ?Tax
    {
        return Cache::remember(
            self::CACHE_PREFIX . "tax_{$id}",
            self::CACHE_TTL,
            fn() => Tax::find($id)
        );
    }

    /**
     * Obtener TypeCurrency por ID (cacheado)
     */
    public static function getTypeCurrency(int $id): ?TypeCurrency
    {
        return Cache::remember(
            self::CACHE_PREFIX . "type_currency_{$id}",
            self::CACHE_TTL,
            fn() => TypeCurrency::find($id)
        );
    }

    /**
     * Obtener UnitMeasure por ID (cacheado)
     */
    public static function getUnitMeasure(int $id): ?UnitMeasure
    {
        return Cache::remember(
            self::CACHE_PREFIX . "unit_measure_{$id}",
            self::CACHE_TTL,
            fn() => UnitMeasure::find($id)
        );
    }

    /**
     * Obtener todos los TypeDocuments (cacheado)
     */
    public static function getAllTypeDocuments()
    {
        return Cache::remember(
            self::CACHE_PREFIX . "all_type_documents",
            self::CACHE_TTL,
            fn() => TypeDocument::all()->keyBy('id')
        );
    }

    /**
     * Obtener todos los Taxes (cacheado)
     */
    public static function getAllTaxes()
    {
        return Cache::remember(
            self::CACHE_PREFIX . "all_taxes",
            self::CACHE_TTL,
            fn() => Tax::all()->keyBy('id')
        );
    }

    /**
     * Limpiar todo el cache de catálogos
     */
    public static function clearAll(): void
    {
        $keys = [
            'all_type_documents',
            'all_taxes',
        ];

        foreach ($keys as $key) {
            Cache::forget(self::CACHE_PREFIX . $key);
        }

        // Limpiar cache por patrón (si Redis soporta)
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $prefix = config('cache.prefix') . ':' . self::CACHE_PREFIX;
                $keys = $redis->keys($prefix . '*');
                if (!empty($keys)) {
                    $redis->del($keys);
                }
            } catch (\Exception $e) {
                // Si falla, ignorar silenciosamente
            }
        }
    }

    /**
     * Precalentar cache de catálogos más usados
     */
    public static function warmUp(): void
    {
        // Cargar todos los type documents
        self::getAllTypeDocuments();
        
        // Cargar todos los taxes
        self::getAllTaxes();
        
        // Cargar type operations más comunes
        foreach ([10, 22, 23, 30, 32] as $id) {
            self::getTypeOperation($id);
        }
        
        // Cargar payment forms
        foreach ([1, 2] as $id) {
            self::getPaymentForm($id);
        }
        
        // Cargar payment methods más comunes
        foreach ([1, 10, 42, 47, 48, 49] as $id) {
            self::getPaymentMethod($id);
        }
    }
}
