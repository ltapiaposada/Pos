@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar categoria</h1>
                <p class="page-subtitle">Actualiza informacion de la categoria</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                @include('categories._form', ['category' => $category])
            </form>
        </div>
    </div>
@endsection
