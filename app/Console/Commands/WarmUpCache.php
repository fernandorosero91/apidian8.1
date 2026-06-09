<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CatalogCacheService;

/**
 * Comando para precalentar el cache de catálogos
 * 
 * Útil para ejecutar después de un deploy o reinicio de Redis
 */
class WarmUpCache extends Command
{
    protected $signature = 'cache:warmup {--clear : Limpiar cache antes de precalentar}';
    
    protected $description = 'Precalienta el cache de catálogos DIAN para mejor rendimiento';

    public function handle()
    {
        $this->info('Precalentando cache de catálogos DIAN...');
        
        if ($this->option('clear')) {
            $this->info('Limpiando cache existente...');
            CatalogCacheService::clearAll();
        }

        $start = microtime(true);
        
        CatalogCacheService::warmUp();
        
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        
        $this->info("Cache precalentado exitosamente en {$elapsed}ms");
        $this->info('Catálogos cacheados: TypeDocuments, Taxes, TypeOperations, PaymentForms, PaymentMethods');
        
        return Command::SUCCESS;
    }
}
