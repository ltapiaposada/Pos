@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nueva categoria</h1>
                <p class="page-subtitle">Crea una categoria para clasificar productos</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @include('categories._form', ['category' => new \App\Models\Category()])
            </form>
        </div>
    </div>
@endsection
