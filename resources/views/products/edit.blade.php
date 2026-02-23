@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar producto</h1>
                <p class="page-subtitle">Actualiza informacion del producto</p>
            </div>
        </div>
    </div>
    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('products._form', ['product' => $product])
            </form>
        </div>
    </div>
@endsection
