<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #4e73df; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 22px; color: #4e73df; }
        .header p { margin: 5px 0 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 7px; text-align: left; vertical-align: top; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 3px 7px; border-radius: 4px; color: white; font-size: 10px; }
        .badge-pending { background: #ffc107; color: #212529; }
        .badge-completed { background: #28a745; }
        .badge-cancelled { background: #dc3545; }
        .footer { margin-top: 40px; text-align: right; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ $title }}</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }} | Tasa paralela promedio usada: {{ number_format($totalParallelRateAvg, 2, ',', '.') }} Bs./USD</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Artículos</th>
                <th>Monto Pagado (USD)</th>
                <th>Costo Real (Cálculo BCV)</th>
                <th>Tasa Paralela</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
                <tr>
                    <td class="text-center">{{ $purchase->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                    <td>{{ $purchase->supplier->first_name }} {{ $purchase->supplier->last_name }}</td>
                    <td class="text-center">{{ $purchase->items->sum('quantity') }}</td>
                    <td class="text-right">$ {{ number_format($purchase->total_amount, 2, ',', '.') }}</td>
                    <td class="text-right">$ {{ number_format($purchase->real_total_bcv ?? $purchase->total_amount * $purchase->bcv_rate_used, 2, ',', '.') }}</td>
                    <td class="text-right">
                        @if($purchase->parallel_rate_used > 0)
                            {{ number_format($purchase->parallel_rate_used, 2, ',', '.') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        @switch($purchase->status)
                            @case('completed')
                                <span class="badge badge-completed">COMPLETADA</span>
                                @break
                            @case('pending')
                                <span class="badge badge-pending">PENDIENTE</span>
                                @break
                            @case('cancelled')
                                <span class="badge badge-cancelled">CANCELADA</span>
                                @break
                        @endswitch
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">TOTALES →</th>
                <th class="text-right">$ {{ number_format($totalUsd, 2, ',', '.') }}</th>
                <th class="text-right">$ {{ number_format($totalRealBcv, 2, ',', '.') }}</th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Costo total real en dólares (BCV): <strong>$ {{ number_format($totalRealBcv, 2, ',', '.') }} </strong>
    </div>
</body>
</html>