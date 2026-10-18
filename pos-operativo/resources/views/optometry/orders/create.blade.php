@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Nueva orden medica</h1>
            <p class="page-subtitle">Documento utilizable en consulta y venta</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.orders.store') }}">
                @include('optometry.orders._form', ['order' => new \App\Models\MedicalOrder()])
            </form>
        </div>
    </div>
@endsection
