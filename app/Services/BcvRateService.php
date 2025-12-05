<?php

namespace App\Services;

use App\Models\DolarBcv;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BcvRateService
{
    // Devuelve siempre la última tasa que esté en la DB
    public function getRate(): float
    {
        $rate = DolarBcv::orderByDesc('fecha')
            ->orderByDesc('hora')
            ->first()?->tasa;

        // Si por algún motivo la DB está vacía (primera vez), fuerza fetch
        return $rate ?? $this->refresh();
    }

    // Fuerza petición a la API y guarda en DB (botón manual o scheduler)
    public function refresh(): float
    {
        try {
            $response = Http::timeout(12)
                ->withUserAgent('InventorySystem/1.0')
                ->get('https://ve.dolarapi.com/v1/dolares/oficial');

            if (!$response->successful() || !isset($response['promedio']) || $response['promedio'] <= 0) {
                throw new \Exception('Respuesta inválida de la API');
            }

            $rate = (float) $response['promedio'];
            $now  = Carbon::now('America/Caracas');

            DolarBcv::updateOrCreate(
                ['fecha' => $now->toDateString(), 'hora' => $now->format('H:i:s')],
                ['tasa' => $rate]
            );

            Log::info('BCV actualizada manualmente', ['tasa' => $rate]);

            return $rate;
        } catch (\Throwable $e) {
            Log::error('Error actualizando BCV', ['error' => $e->getMessage()]);
            // Si falla la API, devuelve la última que sí exista en DB
            return DolarBcv::orderByDesc('fecha')
                ->orderByDesc('hora')
                ->first()?->tasa ?? 243.1105; // solo la primera vez ever
        }
    }
}