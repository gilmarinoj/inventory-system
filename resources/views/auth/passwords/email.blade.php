@extends('layouts.auth')

@section('content')

<p class="login-box-msg">Olvidó su contraseña? Ingrese su correo y recuperela!</p>

<form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div class="form-group">

        <div class="input-group">
            <input type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Correo"
                name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>
        @error('email')
        <span class="invalid-feedback" role="alert">
            <strong>El correo que ingresó es incorrecto</strong>
        </span>
        @enderror
    </div>
    <div class="row">
        <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Solicitar cambio de contraseña</button>
        </div>
        <!-- /.col -->
    </div>
</form>

<p class="mt-3 mb-1">
    <a href="{{ route('login') }}">Iniciar sesión</a>
</p>
<p class="mb-0">
    <a href="{{ route('register') }}" class="text-center">Registrar un nuevo usuario</a>
</p>

@endsection