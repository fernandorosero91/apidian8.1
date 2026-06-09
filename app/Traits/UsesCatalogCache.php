<?php

namespace App\Traits;

use App\Services\CatalogCacheService;
use App\TypeDocument;
use App\TypeOperation;
use App\PaymentForm;
use App\PaymentMethod;
use App\Municipality;

/**
 * Trait para usar cache de catálogos en controladores
 * 
 * Proporciona métodos helper que usan cache automáticamente
 * manteniendo compatibilidad con el código existente.
 */
trait UsesCatalogCache
{
    /**
     * Obtener TypeDocument con cache (compatible con findOrFail)
     */
    protected function getCachedTypeDocument(int $id): TypeDocument
    {
        $result = CatalogCacheService::getTypeDocument($id);
        
        if (!$result) {
            // Fallback a consulta directa si no está en cache
            return TypeDocument::findOrFail($id);
        }
        
        return $result;
    }

    /**
     * Obtener TypeOperation con cache (compatible con findOrFail)
     */
    protected function getCachedTypeOperation(int $id): TypeOperation
    {
        $result = CatalogCacheService::getTypeOperation($id);
        
        if (!$result) {
            return TypeOperation::findOrFail($id);
        }
        
        return $result;
    }

    /**
     * Obtener PaymentForm con cache
     */
    protected function getCachedPaymentForm(int $id): PaymentForm
    {
        $result = CatalogCacheService::getPaymentForm($id);
        
        if (!$result) {
            return PaymentForm::findOrFail($id);
        }
        
        return $result;
    }

    /**
     * Obtener PaymentMethod con cache
     */
    protected function getCachedPaymentMethod(int $id): PaymentMethod
    {
        $result = CatalogCacheService::getPaymentMethod($id);
        
        if (!$result) {
            return PaymentMethod::findOrFail($id);
        }
        
        return $result;
    }

    /**
     * Obtener Municipality por codefacturador con cache
     */
    protected function getCachedMunicipalityByCode(string $code): ?Municipality
    {
        return CatalogCacheService::getMunicipalityByCode($code);
    }

    /**
     * Procesar payment forms con cache
     * Reemplaza el loop común en los controladores
     */
    protected function processPaymentFormsWithCache(array $paymentFormAll, array $default = null): \Illuminate\Support\Collection
    {
        $defaultPayment = $default ?? [
            'payment_form_id' => 1,
            'payment_method_id' => 10,
        ];

        $paymentForm = collect();
        
        foreach ($paymentFormAll ?? [$defaultPayment] as $paymentF) {
            $payment = $this->getCachedPaymentForm($paymentF['payment_form_id']);
            $paymentMethod = $this->getCachedPaymentMethod($paymentF['payment_method_id']);
            
            $payment['payment_method_code'] = $paymentMethod->code;
            $payment['nameMethod'] = $paymentMethod->name;
            $payment['payment_due_date'] = $paymentF['payment_due_date'] ?? null;
            $payment['duration_measure'] = $paymentF['duration_measure'] ?? null;
            
            $paymentForm->push($payment);
        }
        
        return $paymentForm;
    }
}
