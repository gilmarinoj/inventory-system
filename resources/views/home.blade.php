@extends('layouts.admin')
@section('content-header', __('dashboard.title'))
@section('content')
    <div class="container-fluid">
        <div class="row">

            <!-- INGRESOS TOTALES -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($income, 2) }}</h3>
                        <p class="mb-0">{{ __('dashboard.Income') }}</p>
                        <small class="text-white opacity-75">
                            Precio Bs: {{ number_format($income * $dolar_bcv, 2, ',', '.') }}
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    <a href="{{ route('orders.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- INGRESOS DE HOY -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>$ {{ number_format($income_today, 2) }}</h3>
                        <p class="mb-0">{{ __('dashboard.Income_Today') }}</p>
                        <small class="text-white opacity-75">
                            Precio Bs: {{ number_format($income_today * $dolar_bcv, 2, ',', '.') }}
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    <a href="{{ route('orders.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- GASTOS DE HOY -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$ {{ number_format($expenses_today ?? 0, 2) }}</h3>
                        <p class="mb-0">Gastos de Hoy</p>
                        <small class="text-white opacity-75">
                            Precio Bs: {{ number_format(($expenses_today ?? 0) * $dolar_bcv, 2, ',', '.') }}
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-minus-circle"></i></div>
                    <a href="{{ route('purchases.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- TOTAL DE GASTOS -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>$ {{ number_format($expenses_total ?? 0, 2) }}</h3>
                        <p class="mb-0">Total de Gastos</p>
                        <small class="text-white opacity-75">
                            Precio Bs: {{ number_format(($expenses_total ?? 0) * $dolar_bcv, 2, ',', '.') }}
                        </small>
                    </div>
                    <div class="icon"><i class="fas fa-minus-circle"></i></div>
                    <a href="{{ route('purchases.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Tus cajas originales -->
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $orders_count }}</h3>
                        <p>{{ __('dashboard.Orders_Count') }}</p>
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
                        <p>{{ __('dashboard.Customers_Count') }}</p>
                    </div>
                    <div class="icon"><i class="fas fa-users nav-icon"></i></div>
                    <a href="{{ route('customers.index') }}" class="small-box-footer">{{ __('common.More_info') }} <i
                            class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <!-- Conversión automática -->
            @php
                $usdToBs = fn($usd) => number_format($usd * $dolar_bcv, 2, ',', '.');
            @endphp

            <div class="row mt-4">
                @foreach ([['Producto con bajo Stock', $low_stock_products], ['Productos más vendidos (mes actual)', $current_month_products], ['Productos más vendidos del año', $past_months_products], ['Los más vendidos (general)', $best_selling_products]] as [$titulo, $productos])
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
                                                <td><img class="product-img" src="{{ Storage::url($p->image) }}"
                                                        alt=""></td>
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
        </div>
    @endsection
