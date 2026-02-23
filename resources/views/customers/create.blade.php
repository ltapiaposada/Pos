@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nuevo contacto</h1>
                <p class="page-subtitle">Registra un contacto para ventas y compras</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('customers.store') }}" method="POST">
                @include('customers._form', ['customer' => new \App\Models\Customer()])
            </form>
        </div>
    </div>
@endsection
