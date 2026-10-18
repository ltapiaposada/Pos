@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Crear empresa</h1>
                <p class="page-subtitle">Alta completa de empresa, administrador inicial y suscripción base.</p>
            </div>
        </div>
    </div>

    <form action="{{ route('system.companies.store') }}" method="POST" class="mt-6">
        @include('system.companies._form')
    </form>
@endsection
