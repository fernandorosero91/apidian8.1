<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Services\DianConnectionService;

/**
 * Controller para verificar el estado de salud de la API
 * 
 * Útil para balanceadores de carga y monitoreo
 */
class HealthCheckController extends Controller
{
    /**
     * Health check básico - solo verifica que la API responde
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function ping()
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Health check completo - verifica todos los servicios
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        $checks = [
            'api' => true,
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
            'cache' => $this->checkCache(),
        ];

        $allHealthy = !in_array(false, $checks, true);

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'degraded',
            'checks' => $checks,
            'services' => [
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_driver' => config('queue.default'),
            ],
            'version' => config('app.name') . env('APP_VERSION', ''),
            'environment' => config('app.env'),
            'timestamp' => now()->toIso8601String(),
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Health check de servicios DIAN
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dianStatus()
    {
        $dianStats = DianConnectionService::getStats();
        
        // Verificar estado actual de cada ambiente
        $dianChecks = [];
        foreach ($dianStats as $env => $info) {
            $dianChecks[$env] = [
                'url' => $info['url'],
                'available' => DianConnectionService::isAvailable($info['url']),
                'cached' => $info['cached'],
            ];
        }

        $allDianUp = collect($dianChecks)->every(fn($check) => $check['available']);

        return response()->json([
            'status' => $allDianUp ? 'all_services_up' : 'some_services_down',
            'dian_services' => $dianChecks,
            'timestamp' => now()->toIso8601String(),
        ], $allDianUp ? 200 : 503);
    }

    /**
     * Verificar conexión a Redis
     */
    private function checkRedis(): bool
    {
        try {
            $response = Redis::ping();
            return $response == 'PONG' || $response === true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar conexión a base de datos
     */
    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar acceso a storage
     */
    private function checkStorage(): bool
    {
        try {
            $testFile = storage_path('app/.health_check');
            file_put_contents($testFile, 'test');
            $content = file_get_contents($testFile);
            unlink($testFile);
            return $content === 'test';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Verificar sistema de cache
     */
    private function checkCache(): bool
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, 'test', 10);
            $value = Cache::get($key);
            Cache::forget($key);
            return $value === 'test';
        } catch (\Exception $e) {
            return false;
        }
    }
}
