<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="{{ route('home') }}" role="button">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="{{ route('home') }}" class="nav-link">{{ __('dashboard.title') }}</a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">

        <li class="nav-item mr-3 mt-2">
            <span class="text-md" id="bcv-rate">
                BCV: {{ number_format($dolar_bcv, 4, ',', '.') }} Bs.
            </span>
            <a href="javascript:void(0)" onclick="refreshBcvRate()" class="ml-1 text-info" title="Actualizar tasa BCV">
                <i class="fas fa-sync-alt" id="bcv-spinner"></i>
            </a>
        </li>

        <li class="nav-item mr-5 mt-2">
            <span class="text-md">Paralelo: 15 Bs.</span>
        </li>

        <script>
            function refreshBcvRate() {
                const spinner = document.getElementById('bcv-spinner');
                const rateEl = document.getElementById('bcv-rate');

                spinner.classList.add('fa-spin');

                fetch('{{ route('bcv.refresh') }}')
                    .then(r => r.json())
                    .then(data => {
                        if (data.rate) {
                            rateEl.textContent = 'BCV: ' + data.rate + ' Bs.';
                        }
                    })
                    .finally(() => {
                        spinner.classList.remove('fa-spin');
                    });
            }
        </script>



        <!-- User Account Dropdown -->
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="fas fa-user-circle"></i> {{ auth()->user()->getFullname() }}
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('settings.index') }}" class="dropdown-item">
                    <i class="nav-icon fas fa-cogs mr-2"></i> {{ __('settings.title') }}
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="dropdown-item"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> {{ __('common.Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>
<!-- /.navbar -->
