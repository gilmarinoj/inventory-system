@extends('layouts.admin')
@section('content')
    <div class="container-fluid">
        <div class="row">

            <!-- INGRESOS DE HOY -->
            <div class="col-12">
                <h2>Resumen Hoy</h2>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($income_today, 2) }}</h3>
                        <p>Ingresos de Hoy</p>
                        <small class="text-white opacity-75">
                            {{ number_format($income_today * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>

            <!-- GASTOS DE HOY -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$ {{ number_format($expenses_today, 2) }}</h3>
                        <p>Gastos de Hoy</p>
                        <small class="text-white opacity-75">
                            {{ number_format($expenses_today * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>

            <div class="col-12">
                <h2>Resumen Mensual</h2>
            </div>

            <!-- INGRESOS DEL MES -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($income_month, 2) }}</h3>
                        <p>Ingresos del Mes</p>
                        <small class="text-white opacity-75">
                            {{ number_format($income_month * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>

            <!-- GASTOS DEL MES -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$ {{ number_format($expenses_month, 2) }}</h3>
                        <p>Gastos del Mes</p>
                        <small class="text-white opacity-75">
                            {{ number_format($expenses_month * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                </div>
            </div>

            <!-- GANANCIA DEL MES (Ingresos - Gastos) -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($profit_month, 2) }}</h3>
                        <p>Ganancia del Mes</p>
                        <small class="text-white opacity-75">
                            {{ number_format($profit_month * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>

            <div class="col-12">
                <h2>Resumen Histórico</h2>
            </div>
            <!-- INGRESOS HISTÓRICOS -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($income_total, 2) }}</h3>
                        <p>Ingresos Totales</p>
                        <small class="text-white opacity-75">
                            {{ number_format($income_total * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>

            <!-- GASTOS HISTÓRICOS -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$ {{ number_format($expenses_total, 2) }}</h3>
                        <p>Gastos Totales</p>
                        <small class="text-white opacity-75">
                            {{ number_format($expenses_total * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>

            <!-- GANANCIA HISTÓRICA -->
            <div class="col-lg-3 col-6">
                <div class="small-box {{ $profit_total >= 0 ? 'bg-success' : 'bg-danger' }}">
                    <div class="inner">
                        <h3>$ {{ number_format($profit_total, 2) }}</h3>
                        <p>Ganancia Histórica</p>
                        <small class="text-white opacity-75">
                            {{ number_format($profit_total * $dolar_paralelo, 2, ',', '.') }} Bs.
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>

            <div class="col-12 mt-2">
            </div>

            <!-- Tus cajas originales -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $orders_count }}</h3>
                        <p>Total de Ventas</p>
                    </div>
                    <div class="icon"><i class="fas fa-receipt"></i></div>
                    <a href="{{ route('orders.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $customers_count }}</h3>
                        <p>Total de Clientes</p>
                    </div>
                    <div class="icon"><i class="fas fa-users nav-icon"></i></div>
                    <a href="{{ route('customers.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Conversión automática -->
            @php
                $usdToBs = fn($usd) => number_format($usd * $dolar_paralelo, 2, ',', '.');
            @endphp

            <div class="row mt-4">
                @foreach ([['Producto con bajo Stock', $low_stock_products], ['Productos más vendidos (mes actual)', $current_month_products], ['Productos más vendidos del año', $current_year_products], ['Los más vendidos (general)', $best_selling_products]] as [$titulo, $productos])
                    <div class="col-md-6 mb-4">
                        <h3>{{ $titulo }}</h3>
                        <div class="card">
                            <div class="card-body p-0">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nombre</th>
                                            <th>Imagen</th>
                                            <th>Código</th>
                                            <th class="text-center">Precio</th>
                                            <th>Cantidad</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($productos as $p)
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>{{ $p->name }}</td>
                                                <td><img class="product-img" src="{{ $p->image_url }}" alt="{{ $p->name }}" height="40"></td>
                                                <td>{{ $p->barcode }}</td>
                                                <td class="text-center">
                                                    <div class="text-success font-weight-bold">$
                                                        {{ number_format($p->price, 2, ',', '.') }}</div>
                                                    <small class="text-muted">{{ $usdToBs($p->price) }} Bs.</small>
                                                </td>
                                                <td>{{ $p->quantity }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $p->status ? 'success' : 'danger' }}">
                                                        {{ $p->status ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>
                        Ventas por Mes - Últimos 12 Meses
                    </h3>
                    <div class="card-tools">
                        <br>
                        <span class="badge badge-light">
                            Total: {{ array_sum($ordersCount) }} órdenes
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="salesLast12Months" height="200"></canvas>
                </div>
            </div>
        </div>
    @endsection

    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('salesLast12Months').getContext('2d');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: @json($months),
                        datasets: [{
                            label: 'Cantidad de Ventas',
                            data: @json($ordersCount),
                            backgroundColor: 'rgba(0, 123, 255, 0.7)',
                            borderColor: '#007bff',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Ventas: ' + context.parsed.y + ' órdenes';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    font: {
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endsection
