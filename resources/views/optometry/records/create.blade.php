@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Nueva historia clinica</h1>
            <p class="page-subtitle">Documento base para seguimiento y orden medica</p>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body">
            <form method="POST" action="{{ route('optometry.records.store') }}">
                @include('optometry.records._form', ['record' => new \App\Models\ClinicalRecord()])
            </form>
        </div>
    </div>
@endsection
