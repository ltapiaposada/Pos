@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Asiento {{ $entry->entry_number }}</h1>
                <p class="page-subtitle">{{ $entry->entry_date->format('Y-m-d') }} - {{ $entry->description }}</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline btn-sm">Volver</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Cuenta</th>
                            <th>Descripcion</th>
                            <th class="text-right">Debe</th>
                            <th class="text-right">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entry->lines as $line)
                            <tr>
                                <td>{{ $line->account->code }} - {{ $line->account->name }}</td>
                                <td>{{ $line->description }}</td>
                                <td class="text-right">{{ number_format((float) $line->debit, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $line->credit, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right">Totales</th>
                            <th class="text-right">{{ number_format((float) $entry->lines->sum('debit'), 2) }}</th>
                            <th class="text-right">{{ number_format((float) $entry->lines->sum('credit'), 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
@endsection

