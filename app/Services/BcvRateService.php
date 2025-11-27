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
            return $this->getBcvDolar();
        });
    }

    private function getBcvDolar()
    {
        try {
            // Nueva API: DolarAPI (JSON estable, sin scraping)
            $endpoint = 'https://dolarapi.com/v1/dolares';
            $ch = curl_init($endpoint);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; InventorySystem/1.0)'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                Log::warning('DolarAPI: Respuesta inválida (HTTP ' . $httpCode . ')');
                throw new \Exception('API respuesta falló');
            }

            $data = json_decode($response, true);
            if (!is_array($data) || empty($data)) {
                Log::warning('DolarAPI: JSON vacío');
                throw new \Exception('Parse JSON falló');
            }

            // Busca el objeto BCV (fuente: "BCV")
            $bcvRate = null;
            foreach ($data as $item) {
                if (isset($item['fuente']) && strtoupper($item['fuente']) === 'BCV') {
                    $bcvRate = (float) $item['promedio']; // O usa 'venta' si prefieres
                    break;
                }
            }

            if (!$bcvRate || $bcvRate <= 0) {
                Log::warning('DolarAPI: No se encontró tasa BCV válida');
                throw new \Exception('Tasa BCV no encontrada');
            }

            // Guarda como última conocida
            Cache::put('bcv_last_known_rate', $bcvRate, now()->addDays(30));

            Log::info('Tasa BCV actualizada vía DolarAPI', ['dolar' => $bcvRate]);
            return $bcvRate;
        } catch (\Exception $e) {
            Log::error('Error DolarAPI BCV: ' . $e->getMessage());
            return $this->getLastKnownRate();
        }
    }

    private function getLastKnownRate()
    {
        return Cache::get('bcv_last_known_rate', 243.1105); // fallback final
    }


    // Para forzar actualización manual (desde el navbar o comando)
    public function refresh()
    {
        Cache::forget($this->cacheKey);
        Cache::forget('bcv_last_known_rate');
        return $this->getDollarRate(); // Fuerza nueva consulta
    }
}
