@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar usuario</h1>
                <p class="page-subtitle">Actualizar datos y permisos</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('security.users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('security.users._form', ['user' => $user])
            </form>
        </div>
    </div>
@endsection
