<?php

namespace App\Providers;

use App\Services\BcvRateService;
use Illuminate\View\View;

class AdminComposer
{
    public function compose(View $view)
    {
        $bcvService = new BcvRateService();
        $dolar_bcv = $bcvService->getDollarRate();  // fallback si falla

        $view->with('dolar_bcv', $dolar_bcv);
    }
}