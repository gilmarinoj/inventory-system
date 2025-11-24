<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BcvRateService
{
    protected $cacheKey = 'bcv_dollar_rate';
    protected $cacheMinutes = 360; // 6 horas (puedes bajar a 60 si quieres cada hora)

    public function getDollarRate()
    {
        return Cache::remember($this->cacheKey, $this->cacheMinutes * 60, function () {
            return $this->fetchFromBcvWithFallback();
        });
    }

    private function fetchFromBcvWithFallback()
    {
        try {
            $html = $this->getBcvHtml();

            if (!$html) {
                Log::warning('BCV: No se pudo obtener HTML');
                return $this->getLastKnownRate();
            }

            $rates = $this->parseRates($html);

            if (!$rates || empty($rates['dolar'])) {
                Log::warning('BCV: No se encontraron tasas');
                return $this->getLastKnownRate();
            }

            // Guardamos también como "última conocida" por si falla después
            Cache::put('bcv_last_known_rate', $rates['dolar'], now()->addDays(30));

            Log::info('Tasa BCV actualizada', ['dolar' => $rates['dolar']]);
            return $rates['dolar'];

        } catch (\Exception $e) {
            Log::error('Error crítico BCV: ' . $e->getMessage());
            return $this->getLastKnownRate();
        }
    }

    private function getLastKnownRate()
    {
        return Cache::get('bcv_last_known_rate', 243.1105); // fallback final
    }

    // Tu código original (cURL + DOM) – lo dejamos igual
    private function getBcvHtml()
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://www.bcv.org.ve/estadisticas/tipo-de-cambio-oficial-del-bcv',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: false;
    }

    private function parseRates($html)
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        $parentDiv = $xpath->query("//div[contains(@class, 'view-tipo-de-cambio-oficial-del-bcv')]")->item(0);
        if (!$parentDiv) return false;

        $rows = $xpath->query(".//div[contains(@class, 'views-row')]", $parentDiv);
        foreach ($rows as $row) {
            $id = $row->getAttribute('id');
            if (strpos($id, 'dolar') !== false) {
                $strong = $xpath->query(".//strong", $row)->item(0);
                if ($strong) {
                    $value = trim($strong->textContent);
                    $value = str_replace(',', '.', $value);
                    return floatval($value);
                }
            }
        }

        return false;
    }

    // Para forzar actualización manual (desde el navbar o comando)
    public function refresh()
    {
        Cache::forget($this->cacheKey);
        return $this->getDollarRate();
    }
}