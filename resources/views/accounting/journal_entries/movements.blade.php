@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Todos los movimientos</h1>
                <p class="page-subtitle">Detalle de cada linea contable registrada</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('accounting.entries.index') }}" class="btn btn-outline btn-sm">Volver a libro diario</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Asiento</th>
                            <th>Cuenta</th>
                            <th>Detalle</th>
                            <th>Usuario</th>
                            <th class="text-right">Debe</th>
                            <th class="text-right">Haber</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lines as $line)
                            <tr>
                                <td>{{ optional($line->journalEntry?->entry_date)->format('Y-m-d') ?? '-' }}</td>
                                <td>
                                    @if ($line->journalEntry)
                                        <a href="{{ route('accounting.entries.show', $line->journalEntry) }}" class="link link-primary">
                                            {{ $line->journalEntry->entry_number }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $line->account?->code }} - {{ $line->account?->name }}</td>
                                <td>{{ $line->description ?: ($line->journalEntry?->description ?? '-') }}</td>
                                <td>{{ $line->journalEntry?->user?->name ?? '-' }}</td>
                                <td class="text-right">{{ number_format((float) $line->debit, 2) }}</td>
                                <td class="text-right">{{ number_format((float) $line->credit, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60">Sin movimientos contables registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($lines->count() > 0)
                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-right">Totales de esta pagina</th>
                                <th class="text-right">{{ number_format($pageDebit, 2) }}</th>
                                <th class="text-right">{{ number_format($pageCredit, 2) }}</th>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $lines->links() }}
    </div>
@endsection
