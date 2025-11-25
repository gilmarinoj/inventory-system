@extends('layouts.admin')

@section('title', __('order.title'))

<script>
    window.dolarBcv = {{ $dolar_bcv }};
</script>

@section('content')
    <div id="cart"></div>
    <!--cart></cart-->

@endsection
