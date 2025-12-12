<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use PDF;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalesReportController extends Controller
{
    public function download(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate   = Carbon::parse($request->end_date)->endOfDay();

        $orders = Order::with(['items.product', 'customer', 'payments'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->latest()
            ->get();

        if ($orders->isEmpty()) {
            return redirect()->back()->with('error', 'No hay ventas en ese rango de fechas.');
        }

        $totalUsd         = $orders->sum->total();
        $totalReceivedUsd = $orders->sum->receivedAmount();
        $totalBs          = $orders->sum->totalHistoricalBsd();
        $receivedBs       = $orders->sum->receivedHistoricalBsd();

        $title = 'REPORTE DE VENTAS - ' . $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y');

        $pdf = PDF::loadView('admin.orders.pdf.report', compact(
            'orders', 'title', 'totalUsd', 'totalReceivedUsd', 'totalBs', 'receivedBs'
        ));

        return $pdf->download('ventas-' . $startDate->format('Y-m-d') . '_al_' . $endDate->format('Y-m-d') . '.pdf');
    }
}