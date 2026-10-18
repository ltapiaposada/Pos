@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Editar cuenta contable</h1>
                <p class="page-subtitle">Actualiza la cuenta del PUC</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounting.accounts.update', $account) }}">
                @csrf
                @method('PUT')
                @include('accounting.accounts._form', ['account' => $account])
            </form>
        </div>
    </div>
@endsection

