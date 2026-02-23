@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Mi perfil</h1>
                <p class="page-subtitle">Actualiza tus datos personales y la seguridad de tu cuenta.</p>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="panel">
            <div class="panel-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="panel">
            <div class="panel-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

@endsection
