@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar sucursal</h1>
                <p class="page-subtitle">Actualiza los datos de la sucursal</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('branches.update', $branch) }}">
                @csrf
                @method('PUT')
                @include('branches._form', ['branch' => $branch])
            </form>
        </div>
    </div>
@endsection
