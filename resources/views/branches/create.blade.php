@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nueva sucursal</h1>
                <p class="page-subtitle">Crea una sucursal para operar ventas</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('branches.store') }}">
                @include('branches._form', ['branch' => new \App\Models\Branch()])
            </form>
        </div>
    </div>
@endsection
