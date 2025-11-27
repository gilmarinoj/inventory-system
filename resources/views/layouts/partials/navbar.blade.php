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

        <li class="mr-1 mt-2">BCV: {{ number_format($dolar_bcv, 4, ',', '.') }} Bs.</li>
        <a href="javascript:void(0)" onclick="fetch('/bcv-refresh').then(r => r.text()).then(() => location.reload())"
            class="small-box-footer mt-2 mr-5" style="cursor:pointer">
            <i class="fas fa-sync-alt"></i>
        </a>

        <li class="mr-1 mt-2">Paralelo: 15 Bs. </li>
        <a href="javascript:void(0)" onclick="fetch('/bcv-refresh').then(r => r.text()).then(() => location.reload())"
            class="small-box-footer mt-2 mr-5" style="cursor:pointer">
            <i class="fas fa-edit"></i>
        </a>

        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
            Launch demo modal
        </button>

        <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>


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
