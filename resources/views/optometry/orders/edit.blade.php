@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar orden medica</h1>
            <p class="page-subtitle">Orden #{{ $order->id }}</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.orders.update', $order) }}">
                @method('PUT')
                @include('optometry.orders._form')
            </form>
        </div>
    </div>
@endsection
