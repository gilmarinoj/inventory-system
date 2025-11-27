<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BcvRateService;

class TestBcvRate extends Command
{
    protected $signature = 'bcv:test';
    protected $description = 'Test BCV rate';

    public function handle()
    {
        $service = new BcvRateService();
        $rate = $service->getDollarRate();
        $this->info("Tasa actual: " . $rate);

        // Fuerza refresh
        $service->refresh();
        $newRate = $service->getDollarRate();
        $this->info("Después de refresh: " . $newRate);
    }
}