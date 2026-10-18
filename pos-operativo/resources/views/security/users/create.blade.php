@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nuevo usuario</h1>
                <p class="page-subtitle">Crear usuario y asignar accesos</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('security.users.store') }}">
                @include('security.users._form', ['user' => new \App\Models\User()])
            </form>
        </div>
    </div>
@endsection
