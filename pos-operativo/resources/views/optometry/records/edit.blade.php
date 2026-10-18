@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Editar historia clinica</h1>
            <p class="page-subtitle">{{ $record->customer?->name }}</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.records.update', $record) }}">
                @method('PUT')
                @include('optometry.records._form')
            </form>
        </div>
    </div>
@endsection
