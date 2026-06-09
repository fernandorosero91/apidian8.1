<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Servicio para gestionar conexiones con la DIAN
 * 
 * Optimiza las verificaciones de estado y conexiones SOAP
 * usando cache para evitar verificaciones repetitivas.
 */
class DianConnectionService
{
    /**
     * Tiempo de cache para estado DIAN (30 segundos)
     * Suficiente para evitar verificaciones repetitivas en ráfagas
     */
    const STATUS_CACHE_TTL = 30;

    /**
     * Tiempo de cache cuando DIAN está caída (5 segundos)
     * Permite reintentos más frecuentes cuando hay problemas
     */
    const STATUS_DOWN_CACHE_TTL = 5;

    /**
     * URLs de los servicios DIAN
     */
    const DIAN_URLS = [
        'production' => 'https://vpfe.dian.gov.co',
        'test' => 'https://vpfe-hab.dian.gov.co',
    ];

    /**
     * Cliente HTTP reutilizable
     */
    private static ?Client $httpClient = null;

    /**
     * Verificar estado de la DIAN con cache
     * 
     * @param string $url URL del servicio DIAN
     * @return bool
     */
    public static function isAvailable(string $url): bool
    {
        $cacheKey = 'dian_status_' . md5($url);
        
        // Verificar cache primero
        $cachedStatus = Cache::get($cacheKey);
        if ($cachedStatus !== null) {
            return $cachedStatus === 'up';
        }

        // Realizar verificación real
        $isUp = self::checkDianStatus($url);
        
        // Cachear resultado
        $ttl = $isUp ? self::STATUS_CACHE_TTL : self::STATUS_DOWN_CACHE_TTL;
        Cache::put($cacheKey, $isUp ? 'up' : 'down', $ttl);
        
        return $isUp;
    }

    /**
     * Verificar estado real de la DIAN
     */
    private static function checkDianStatus(string $url): bool
    {
        try {
            $client = self::getHttpClient();
            
            $response = $client->request('GET', $url, [
                'timeout' => 5,           // Reducido de 10 a 5
                'connect_timeout' => 3,   // Reducido de 10 a 3
                'http_errors' => false,   // No lanzar excepciones por códigos HTTP
            ]);

            // Considerar disponible si responde (incluso con errores HTTP)
            return $response->getStatusCode() < 500;
            
        } catch (ConnectException $e) {
            return false;
        } catch (RequestException $e) {
            // Si hay respuesta, el servidor está vivo
            return $e->hasResponse();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener cliente HTTP reutilizable
     */
    private static function getHttpClient(): Client
    {
        if (self::$httpClient === null) {
            self::$httpClient = new Client([
                'verify' => true,
                'headers' => [
                    'User-Agent' => 'APIDIAN/2.1',
                ],
            ]);
        }
        
        return self::$httpClient;
    }

    /**
     * Forzar recarga del estado (útil después de errores)
     */
    public static function refreshStatus(string $url): bool
    {
        $cacheKey = 'dian_status_' . md5($url);
        Cache::forget($cacheKey);
        
        return self::isAvailable($url);
    }

    /**
     * Obtener estadísticas de conexión
     */
    public static function getStats(): array
    {
        $stats = [];
        
        foreach (self::DIAN_URLS as $env => $url) {
            $cacheKey = 'dian_status_' . md5($url);
            $status = Cache::get($cacheKey);
            
            $stats[$env] = [
                'url' => $url,
                'status' => $status ?? 'unknown',
                'cached' => $status !== null,
            ];
        }
        
        return $stats;
    }

    /**
     * Verificar si es ambiente de producción basado en URL
     */
    public static function isProduction(string $url): bool
    {
        return strpos($url, '-hab') === false;
    }
}
