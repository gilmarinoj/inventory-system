<?php

use App\Services\BcvRateService;

if (!function_exists('usd_to_bs')) {
    function usd_to_bs(float $usd, ?int $decimals = 2): string
    {
        $bcv = new BcvRateService();
        $dolar = $bcv->getDollarRate() ?? 243.1105; // fallback por si falla

        $bs = $usd * $dolar;

        return number_format($bs, $decimals, ',', '.');
    }
}

if (!function_exists('get_dolar_bcv')) {
    function get_dolar_bcv(?int $decimals = 4): string
    {
        $bcv = new BcvRateService();
        $dolar = $bcv->getDollarRate() ?? 243.1105;

        return number_format($dolar, $decimals, ',', '.');
    }
}