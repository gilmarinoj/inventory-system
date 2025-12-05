@extends('layouts.admin')

@section('title', 'All Purchases')

@section('css')
    <style>
        html,
        body {
            height: 100%;
        }

        .wrapper {
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
        }

        .main-footer {
            margin-left: 0 !important;
        }
    </style>
@endsection

@section('content-header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-box text-primary"></i> {{ __('All Purchases') }}
                    </h1>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="h-100 d-flex flex-column" x-data="purchaseFilter()" x-init="init()">

        <!-- Filters Card -->
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter"></i> {{ __('Filters') }}
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Status') }}</label>
                            <select x-model="filters.status" @change="applyFilters()" class="form-control">
                                <option value="">{{ __('All') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="completed">{{ __('Completed') }}</option>
                                <option value="cancelled">{{ __('Cancelled') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>{{ __('Supplier') }}</label>
                            <select x-model="filters.supplier_id" @change="applyFilters()" class="form-control">
                                <option value="">{{ __('All Suppliers') }}</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->first_name }} {{ $supplier->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>{{ __('Date From') }}</label>
                            <input type="date" x-model="filters.date_from" @change="applyFilters()" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>{{ __('Date To') }}</label>
                            <input type="date" x-model="filters.date_to" @change="applyFilters()" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button @click="resetFilters()" class="btn btn-default btn-block">
                                <i class="fas fa-redo"></i> {{ __('Reset') }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label>{{ __('Search') }}</label>
                            <div class="input-group">
                                <input type="text" x-model="filters.search" @input.debounce.500ms="applyFilters()"
                                    class="form-control" placeholder="{{ __('Search by ID or notes...') }}">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Overlay -->
        <div x-show="loading" x-transition class="text-center py-5 flex-shrink-0">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
        </div>

        <!-- Purchases Table Card -->
        <div class="card" x-show="!loading" x-transition style="height: calc(100vh - 280px);">
            <div class="card-body p-0 h-100 d-flex flex-column">
                <div class="table-responsive flex-grow-1">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light sticky-top bg-white">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Artículos</th>
                                <th class="text-center">Tasa</th>
                                <th class="text-center">Monto Total</th>
                                <th class="text-center">Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-if="purchases.data && purchases.data.length > 0">
                                <template x-for="purchase in purchases.data" :key="purchase.id">
                                    <tr>
                                        <td>
                                            <a :href="`/admin/purchases/${purchase.id}`"
                                                class="font-weight-bold text-primary">
                                                #<span x-text="purchase.id"></span>
                                            </a>
                                        </td>
                                        <td x-text="formatDate(purchase.purchase_date)"></td>
                                        <td>
                                            <i class="fas fa-truck text-muted mr-1"></i>
                                            <span
                                                x-text="`${purchase.supplier.first_name} ${purchase.supplier.last_name}`"></span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                <span x-text="purchase.items_count"></span> items
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Tasa BCV del día de la compra (fija para siempre) -->
                                            <div class="text-success font-weight-bold">
                                                <span
                                                    x-text="purchase.bcv_rate_used ? parseFloat(purchase.bcv_rate_used).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '—'"></span>
                                                <small class="d-block text-success">BCV</small>
                                            </div>

                                            <!-- Tasa paralela que te cobró el proveedor -->
                                            <div class="mt-2 text-danger font-weight-bold">
                                                <span
                                                    x-text="purchase.parallel_rate_used ? parseFloat(purchase.parallel_rate_used).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '—'"></span>
                                                <small class="d-block text-danger">Proveedor</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <!-- Monto pagado al proveedor (paralelo) -->
                                            <div class="mb-3">
                                                <strong class="text-success">
                                                    $ <span x-text="parseFloat(purchase.total_amount).toFixed(2)"></span>
                                                </strong>
                                                <br>
                                                <small class="text-muted">
                                                    <span
                                                        x-text="(parseFloat(purchase.total_amount) * {{ $dolar_bcv }}).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></span>
                                                    Bs.
                                                </small>
                                            </div>

                                            <!-- Monto Real BCV (el que entra al inventario) -->
                                            <template
                                                x-if="purchase.real_total_bcv && parseFloat(purchase.real_total_bcv) > 0">
                                                <div class="pt-2 border-top border-danger">
                                                    <strong class="text-danger">
                                                        $ <span
                                                            x-text="parseFloat(purchase.real_total_bcv).toFixed(2)"></span>
                                                    </strong>
                                                    <br>
                                                    <small class="text-danger font-weight-bold">
                                                        Costo Real (BCV)
                                                    </small>
                                                    <br>
                                                    <span class="text-muted text-sm">
                                                        <span
                                                            x-text="(parseFloat(purchase.real_total_bcv) * {{ $dolar_bcv }}).toFixed(2).replace('.',',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></span>
                                                        Bs.
                                                    </span>
                                                </div>
                                            </template>
                                        </td>
                                        <td class="text-center">
                                            <template x-if="purchase.status === 'completed'">
                                                <span class="badge badge-success">Completado</span>
                                            </template>
                                            <template x-if="purchase.status === 'pending'">
                                                <span class="badge badge-warning">Pendiente</span>
                                            </template>
                                            <template x-if="purchase.status === 'cancelled'">
                                                <span class="badge badge-danger">Cancelado</span>
                                            </template>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a :href="`/admin/purchases/${purchase.id}`" class="btn btn-info"><i
                                                        class="fas fa-eye"></i></a>
                                                <a :href="`/admin/purchases/${purchase.id}/receipt`"
                                                    class="btn btn-success" target="_blank"><i
                                                        class="fas fa-print"></i></a>
                                                <button @click="deletePurchase(purchase.id)" class="btn btn-danger"><i
                                                        class="fas fa-trash"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </template>
                            <template x-if="!purchases.data || purchases.data.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                                        <p class="h5 text-muted">No hay compras registradas</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación siempre visible debajo del scroll -->
                <div class="card-footer bg-white border-top-0" x-show="purchases.last_page > 1">
                    <nav>
                        <ul class="pagination pagination-sm justify-content-center m-0">
                            <template x-for="page in paginationPages" :key="page">
                                <li class="page-item"
                                    :class="{ 'active': page === purchases.current_page, 'disabled': page === '...' }">
                                    <a class="page-link" href="#"
                                        @click.prevent="page !== '...' && changePage(page)" x-text="page"></a>
                                </li>
                            </template>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<script>
    function purchaseFilter() {
        return {
            loading: false,
            purchases: {
                data: [],
                current_page: 1,
                last_page: 1,
                total: 0
            },
            filters: {
                status: '{{ request('status') }}',
                supplier_id: '{{ request('supplier_id') }}',
                date_from: '{{ request('date_from') }}',
                date_to: '{{ request('date_to') }}',
                search: '{{ request('search') }}',
                page: 1
            },

            init() {
                this.fetchPurchases();
            },

            async fetchPurchases() {
                this.loading = true;

                const params = new URLSearchParams();
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key]) {
                        params.append(key, this.filters[key]);
                    }
                });

                try {
                    const response = await fetch(`{{ route('purchases.data') }}?${params}`);
                    const data = await response.json();
                    this.purchases = data;
                } catch (error) {
                    console.error('Error fetching purchases:', error);
                } finally {
                    this.loading = false;
                }
            },

            applyFilters() {
                this.filters.page = 1;
                this.fetchPurchases();
                this.updateUrl();
            },

            resetFilters() {
                this.filters = {
                    status: '',
                    supplier_id: '',
                    date_from: '',
                    date_to: '',
                    search: '',
                    page: 1
                };
                this.fetchPurchases();
                window.history.pushState({}, '', '{{ route('purchases.index') }}');
            },

            changePage(page) {
                this.filters.page = page;
                this.fetchPurchases();
                this.updateUrl();
            },

            updateUrl() {
                const params = new URLSearchParams();
                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key]) {
                        params.append(key, this.filters[key]);
                    }
                });
                window.history.pushState({}, '', `{{ route('purchases.index') }}?${params}`);
            },

            async deletePurchase(id) {
                if (!confirm('{{ __('Are you sure?') }}')) return;

                try {
                    const response = await fetch(`/admin/purchases/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });

                    if (response.ok) {
                        this.fetchPurchases();
                    } else {
                        alert('Failed to delete purchase');
                    }
                } catch (error) {
                    console.error('Error deleting purchase:', error);
                    alert('An error occurred');
                }
            },

            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });
            },

            get paginationPages() {
                const pages = [];
                const current = this.purchases.current_page;
                const last = this.purchases.last_page;

                if (last <= 7) {
                    for (let i = 1; i <= last; i++) {
                        pages.push(i);
                    }
                } else {
                    if (current <= 3) {
                        for (let i = 1; i <= 5; i++) pages.push(i);
                        pages.push('...');
                        pages.push(last);
                    } else if (current >= last - 2) {
                        pages.push(1);
                        pages.push('...');
                        for (let i = last - 4; i <= last; i++) pages.push(i);
                    } else {
                        pages.push(1);
                        pages.push('...');
                        for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                        pages.push('...');
                        pages.push(last);
                    }
                }

                return pages;
            }
        }
    }
</script>
