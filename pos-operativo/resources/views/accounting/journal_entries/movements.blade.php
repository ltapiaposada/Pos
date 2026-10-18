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
            <div class="space-y-3 md:hidden">
                @forelse ($lines as $line)
                    <article class="surface-muted rounded-2xl p-4">
                        <p class="text-xs text-base-content/60">{{ optional($line->journalEntry?->entry_date)->format('Y-m-d') ?? '-' }}</p>
                        <p class="mt-1 text-sm font-semibold">
                            @if ($line->journalEntry)
                                <a href="{{ route('accounting.entries.show', $line->journalEntry) }}" class="link link-primary">
                                    {{ $line->journalEntry->entry_number }}
                                </a>
                            @else
                                -
                            @endif
                        </p>
                        <p class="mt-2 text-sm">{{ $line->account?->code }} - {{ $line->account?->name }}</p>
                        <p class="text-xs text-base-content/60">{{ $line->description ?: ($line->journalEntry?->description ?? '-') }}</p>
                        <p class="mt-1 text-xs text-base-content/60">Usuario: {{ $line->journalEntry?->user?->name ?? '-' }}</p>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Debe</p>
                                <p class="font-semibold">{{ number_format((float) $line->debit, 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Haber</p>
                                <p class="font-semibold">{{ number_format((float) $line->credit, 2) }}</p>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin movimientos contables registrados.</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
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
