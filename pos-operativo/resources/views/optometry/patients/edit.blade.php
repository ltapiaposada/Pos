@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar paciente</h1>
            <p class="page-subtitle">{{ $patient->name }}</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.patients.update', $patient) }}">
                @method('PUT')
                @include('optometry.patients._form', ['patient' => $patient])
            </form>
        </div>
    </div>
@endsection
