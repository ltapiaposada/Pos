@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nuevo producto</h1>
                <p class="page-subtitle">Agrega un producto al catalogo</p>
            </div>
        </div>
    </div>
    <div class="mt-6 panel">
        <div class="panel-body">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @include('products._form', ['product' => new \App\Models\Product()])
            </form>
        </div>
    </div>
@endsection
