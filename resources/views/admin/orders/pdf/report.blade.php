<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 11px; }
        .badge-success { background: #28a745; }
        .badge-warning { background: #ffc107; color: #212529; }
        .badge-danger { background: #dc3545; }
        .footer { margin-top: 50px; text-align: right; font-size: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>{{ $title }}</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Items</th>
                <th>Total USD</th>
                <th>Total Bs.</th>
                <th>Recibido USD</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $order)
                <tr>
                    <td class="text-center">{{ $order->id }}</td>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $order->getCustomerName() }}</td>
                    <td class="text-center">{{ $order->items->sum('quantity') }}</td>
                    <td class="text-right">$ {{ number_format($order->total(), 2, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($order->totalHistoricalBsd(), 2, ',', '.') }} Bs.</td>
                    <td class="text-right">$ {{ number_format($order->receivedAmount(), 2, ',', '.') }}</td>
                    <td class="text-center">
                        @if($order->receivedAmount() >= $order->total())
                            <span class="badge badge-success">PAGADO</span>
                        @elseif($order->receivedAmount() > 0)
                            <span class="badge badge-warning">PARCIAL</span>
                        @else
                            <span class="badge badge-danger">PENDIENTE</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">TOTALES</th>
                <th class="text-right">$ {{ number_format($totalUsd, 2, ',', '.') }}</th>
                <th class="text-right">{{ number_format($totalBs, 2, ',', '.') }} Bs.</th>
                <th class="text-right">$ {{ number_format($totalReceivedUsd, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <strong>Total en bolívares históricos:</strong> {{ number_format($receivedBs, 2, ',', '.') }} Bs.
    </div>
</body>
</html>