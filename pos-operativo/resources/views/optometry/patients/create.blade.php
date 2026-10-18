@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Nuevo paciente</h1>
            <p class="page-subtitle">Crea la ficha base para historia clinica y ordenes medicas</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.patients.store') }}">
                @include('optometry.patients._form', ['patient' => new \App\Models\Customer()])
            </form>
        </div>
    </div>
@endsection
