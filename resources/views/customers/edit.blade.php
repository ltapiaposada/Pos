@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar contacto</h1>
                <p class="page-subtitle">Actualiza informacion del contacto</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                @include('customers._form', ['customer' => $customer])
            </form>
        </div>
    </div>
@endsection
