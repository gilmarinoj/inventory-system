<?php

namespace App\Http\Controllers;

use App\Models\DolarParalelo;
use Illuminate\Http\Request;

class ParaleloController extends Controller
{
    public function refresh(Request $request)
    {
        $request->validate([
            'tasa' => 'required|numeric|min:0.01'
        ]);

        $tasa = $request->input('tasa');

        DolarParalelo::create([
            'fecha' => now()->format('Y-m-d'),
            'hora'  => now()->format('H:i:s'),
            'tasa'  => $tasa,
        ]);

        return response()->json([
            'success' => true,
            'rate'    => number_format($tasa, 4, ',', '.')
        ]);
    }
}
