@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Nueva cuenta contable</h1>
                <p class="page-subtitle">Crea una cuenta del PUC</p>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <form method="POST" action="{{ route('accounting.accounts.store') }}">
                @include('accounting.accounts._form', ['account' => new \App\Models\AccountingAccount()])
            </form>
        </div>
    </div>
@endsection

