<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', config('app.name'))</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Ionicons -->
    <!-- <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> -->
    <!-- overlayScrollbars -->
    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">


    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @yield('css')
    <script>
        window.APP = <?php echo json_encode([
            'currency_symbol' => config('settings.currency_symbol'),
            'warning_quantity' => config('settings.warning_quantity'),
        ]); ?>
    </script>
</head>

<body class="hold-transition sidebar-mini">
    <!-- Site wrapper -->
    <div class="wrapper">

        @include('layouts.partials.navbar')
        @include('layouts.partials.sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1>@yield('content-header')</h1>
                        </div>
                        <div class="col-sm-6 text-right">
                            @yield('content-actions')
                        </div><!-- /.col -->
                    </div>
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                @include('layouts.partials.alert.success')
                @include('layouts.partials.alert.error')
                @yield('content')
            </section>

        </div>
        <!-- /.content-wrapper -->

        @include('layouts.partials.footer')

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->
    <!-- <script src="{{ asset('js/app.js') }}"></script> -->


    @yield('js')
    @yield('model')

    {{-- Modal Dólar Paralelo - VERSIÓN QUE FUNCIONA SÍ O SÍ --}}
    <div class="modal fade" id="paraleloModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <form id="paraleloForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Actualizar Dólar Paralelo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nueva tasa (Bs./USD):</label>
                            <input type="number" step="0.01" class="form-control" name="tasa"
                                value="{{ \App\Models\DolarParalelo::tasaActualRaw() }}" required min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Dólar Paralelo - VERSIÓN 100% INFALIBLE CON VITE --}}
    <div class="modal fade" id="paraleloModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <form id="paraleloForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title">Actualizar Dólar Paralelo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nueva tasa (Bs./USD):</label>
                            <input type="number" step="0.01" class="form-control" name="tasa"
                                value="{{ \App\Models\DolarParalelo::tasaActualRaw() }}" required min="0.01">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ESTE META TAG ES LA CLAVE -->
    <meta name="paralelo-refresh-url" content="{{ route('paralelo.refresh') }}">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const refreshUrl = document.querySelector('meta[name="paralelo-refresh-url"]').content;

            window.openParaleloModal = function() {
                $('#paraleloModal').modal('show');
            };

            document.getElementById('paraleloForm').onsubmit = function(e) {
                e.preventDefault();

                const btn = this.querySelector('button[type="submit"]');
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

                fetch(refreshUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            tasa: this.tasa.value
                        })
                    })
                    .then(r => {
                        if (!r.ok) throw r;
                        return r.json();
                    })
                    .then(data => {
                        document.getElementById('paralelo-rate').textContent = data.tasa;
                        $('#paraleloModal').modal('hide');
                        if (typeof toastr !== 'undefined') toastr.success('Dólar paralelo actualizado');
                    })
                    .catch(err => {
                        console.error('Error completo:', err);
                        if (typeof toastr !== 'undefined') toastr.error('Error al guardar');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btn.innerHTML = original;
                    });
            };
        });
    </script>
</body>

</html>
