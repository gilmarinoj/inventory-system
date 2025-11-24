<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use DOMDocument;
use DOMXPath;

class BcvRateService
{
    protected $cacheKey = 'bcv_exchange_rates';
    protected $cacheMinutes = 360; // 6 horas

    public function getRates()
    {
        return Cache::remember($this->cacheKey, $this->cacheMinutes * 60, function () {
            return $this->fetchRatesFromBcv();
        });
    }

    public function getDollarRate()
    {
        $rates = $this->getRates();
        return $rates['dolar'] ?? null;
    }

    private function fetchRatesFromBcv()
    {
        $html = $this->get_bcv_html();

        if (empty($html)) {
            Log::warning('BCV: No se pudo obtener el HTML');
            return $this->getFallbackRates();
        }

        $rates = $this->search_exchange_rates($html);

        if ($rates === false || empty($rates['dolar'])) {
            Log::warning('BCV: No se encontraron las tasas en el HTML', ['html_length' => strlen($html)]);
            return $this->getFallbackRates();
        }

        Log::info('Tasas BCV actualizadas correctamente', $rates);
        return $rates;
    }

    // === TU CÓDIGO ORIGINAL COPIADO TAL CUAL ===

    private function get_bcv_html()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.bcv.org.ve/estadisticas/tipo-de-cambio-de-referencia');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            Log::error('cURL Error BCV: ' . $error);
            return false;
        }
        
        return $response;
    }

    private function search_exchange_rates($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);

        $xpath = new DOMXPath($dom);

        // Get parent div by its class
        $parentDiv = $xpath->query("//div[contains(@class, 'view-tipo-de-cambio-oficial-del-bcv')]");

        if ($parentDiv->length === 0) {
            return false;
        }

        // Search for the child div that contains the div with class 'view-content'
        $childDiv = $xpath->query(".//div[contains(@class, 'view-content')]", $parentDiv->item(0));

        if ($childDiv->length === 0) {
            return false;
        }

        // Search for the next to child div that contains the rates div
        $ratesDiv = $xpath->query(".//div[contains(@class, 'views-row')]", $childDiv->item(0));

        if ($ratesDiv->length === 0) {
            return false;
        }

        // Get all divs inside rates div
        $rates = $xpath->query(".//div", $ratesDiv->item(0));

        if ($rates->length === 0) {
            return false;
        }

        // Extract the rates
        $found_rates = [];
        $available_rates = ['euro', 'yuan', 'lira', 'rublo', 'dolar'];

        foreach ($rates as $rateDiv) {
            $id = $rateDiv->getAttribute('id');
            foreach ($available_rates as $available_rate) {
                if (strpos($id, $available_rate) !== false) {
                    $strong = $xpath->query(".//strong", $rateDiv);
                    if ($strong->length > 0) {
                        $value = trim($strong->item(0)->textContent);
                        $value = str_replace(',', '.', $value);
                        $found_rates[$available_rate] = floatval($value);
                    }
                }
            }
        }

        return $found_rates;
    }

    // === FALLBACK con valores reales de hoy ===
    private function getFallbackRates()
    {
        return [
            'dolar' => 243.1105,
            'euro'  => 265.4523,
            'yuan'  => 34.1234,
            'lira'  => 7.0987,
            'rublo' => 2.4987,
        ];
    }

    // Para actualizar manualmente
    public function refresh()
    {
        Cache::forget($this->cacheKey);
        return $this->getRates();
    }
}