<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Log;
use Carbon\Carbon;

/**
 * Comando para limpiar logs antiguos de la API
 * 
 * Uso: php artisan logs:clean --days=30
 * 
 * Este comando elimina registros de logs más antiguos que los días especificados
 * para mantener la base de datos optimizada.
 */
class CleanOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:clean 
                            {--days=30 : Número de días a mantener}
                            {--dry-run : Simular sin eliminar}
                            {--force : Ejecutar sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar logs de API antiguos para optimizar la base de datos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        
        $cutoffDate = Carbon::now()->subDays($days);
        
        $count = Log::where('created_at', '<', $cutoffDate)->count();
        
        if ($count === 0) {
            $this->info("No hay logs anteriores a {$cutoffDate->format('Y-m-d')} para eliminar.");
            return 0;
        }

        $this->info("Se encontraron {$count} logs anteriores a {$cutoffDate->format('Y-m-d')}");

        if ($dryRun) {
            $this->warn('Modo simulación (--dry-run): No se eliminaron registros.');
            return 0;
        }

        $force = $this->option('force');
        if (!$force && !$this->confirm("¿Desea eliminar {$count} registros de logs?")) {
            $this->info('Operación cancelada.');
            return 0;
        }

        // Eliminar en lotes para evitar bloqueos
        $deleted = 0;
        $batchSize = 1000;

        $this->output->progressStart($count);

        while ($deleted < $count) {
            $batch = Log::where('created_at', '<', $cutoffDate)
                ->limit($batchSize)
                ->delete();
            
            $deleted += $batch;
            $this->output->progressAdvance($batch);

            if ($batch === 0) break;
        }

        $this->output->progressFinish();

        $this->info("Se eliminaron {$deleted} registros de logs exitosamente.");
        
        return 0;
    }
}
