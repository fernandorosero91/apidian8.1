<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Company;
use App\Certificate;
use Carbon\Carbon;

/**
 * Comando para verificar vencimiento de certificados digitales
 * 
 * Uso: php artisan certificates:check
 * 
 * Este comando revisa todos los certificados y alerta sobre los próximos a vencer.
 */
class CheckCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:check 
                            {--days=30 : Días de anticipación para alertar}
                            {--json : Salida en formato JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar vencimiento de certificados digitales';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $warningDays = (int) $this->option('days');
        $jsonOutput = $this->option('json');
        $warningDate = Carbon::now()->addDays($warningDays);

        $certificates = Certificate::with('company')
            ->whereNotNull('expiration_date')
            ->get();

        $results = [
            'expired' => [],
            'warning' => [],
            'ok' => [],
        ];

        foreach ($certificates as $cert) {
            $expirationDate = Carbon::parse($cert->expiration_date);
            $daysLeft = Carbon::now()->diffInDays($expirationDate, false);
            
            $company = Company::where('id', $cert->company_id)->first();
            $companyName = $company ? $company->identification_number : 'N/A';

            $certInfo = [
                'company_nit' => $companyName,
                'certificate' => $cert->name,
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'days_left' => $daysLeft,
            ];

            if ($daysLeft < 0) {
                $results['expired'][] = $certInfo;
            } elseif ($daysLeft <= $warningDays) {
                $results['warning'][] = $certInfo;
            } else {
                $results['ok'][] = $certInfo;
            }
        }

        if ($jsonOutput) {
            $this->line(json_encode($results, JSON_PRETTY_PRINT));
            return count($results['expired']) > 0 ? 1 : 0;
        }

        // Mostrar resultados en tabla
        if (count($results['expired']) > 0) {
            $this->error('=== CERTIFICADOS VENCIDOS ===');
            $this->table(
                ['NIT Empresa', 'Certificado', 'Fecha Vencimiento', 'Días'],
                array_map(fn($c) => array_values($c), $results['expired'])
            );
        }

        if (count($results['warning']) > 0) {
            $this->warn("=== CERTIFICADOS POR VENCER (próximos {$warningDays} días) ===");
            $this->table(
                ['NIT Empresa', 'Certificado', 'Fecha Vencimiento', 'Días Restantes'],
                array_map(fn($c) => array_values($c), $results['warning'])
            );
        }

        $this->info("Resumen: " . count($results['expired']) . " vencidos, " . 
                    count($results['warning']) . " por vencer, " . 
                    count($results['ok']) . " OK");

        return count($results['expired']) > 0 ? 1 : 0;
    }
}
