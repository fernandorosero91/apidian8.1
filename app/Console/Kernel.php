<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Backups automáticos
        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('02:00');

        // Limpieza de logs antiguos (cada domingo a las 3am)
        $schedule->command('logs:clean --days=90 --force')
            ->weekly()
            ->sundays()
            ->at('03:00')
            ->withoutOverlapping();

        // Verificación de certificados (diario a las 8am)
        $schedule->command('certificates:check --days=30')
            ->daily()
            ->at('08:00')
            ->emailOutputOnFailure(env('ADMIN_EMAIL'));

        // Precalentar cache de catálogos (cada 12 horas)
        $schedule->command('cache:warmup')
            ->twiceDaily(6, 18)
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
