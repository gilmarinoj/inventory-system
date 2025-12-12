<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use PDF;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PurchaseReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $purchases = Purchase::with(['supplier', 'items.product'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->get();

        if ($purchases->isEmpty()) {
            return redirect()->back()->with('error', 'No hay compras en ese rango de fechas.');
        }

        $totalUsd             = $purchases->sum('total_amount');
        $totalRealBcv         = $purchases->sum('real_total_bcv');
        $totalParallelRateAvg = $purchases->where('parallel_rate_used', '>', 0)->avg('parallel_rate_used') ?? 0;

        $title = 'REPORTE DE COMPRAS - ' . $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y');

        $pdf = PDF::loadView('admin.purchases.pdf.report', compact(
            'purchases', 'title', 'totalUsd', 'totalRealBcv', 'totalParallelRateAvg'
        ));

        return $pdf->download('compras-' . $startDate->format('Y-m-d') . '_al_' . $endDate->format('Y-m-d') . '.pdf');
    }
}