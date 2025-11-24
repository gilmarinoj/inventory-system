<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateBcvRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bcv:update-rates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $service = new \App\Services\BcvRateService();
        $rates = $service->refresh();

        $this->info('Tasas BCV actualizadas:');
        $this->table(['Moneda', 'Tasa'], collect($rates)->map(fn($v, $k) => [$k, $v]));
    }
}
