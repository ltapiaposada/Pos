@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Historias clinicas</h1>
                <p class="page-subtitle">Registro y seguimiento de consultas optometricas</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar paciente" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('optometry.records.create') }}" class="btn btn-primary btn-sm">Nueva historia</a>
            </div>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->customer?->name }}</td>
                            <td>{{ $record->examined_at->format('d/m/Y H:i') }}</td>
                            <td class="max-w-sm truncate">{{ $record->reason_for_consultation }}</td>
                            <td class="text-right">
                                <div class="actions justify-end">
                                    <a href="{{ route('optometry.records.show', $record) }}" class="btn btn-outline-primary btn-xs">Ver</a>
                                    <a href="{{ route('optometry.records.edit', $record) }}" class="btn btn-outline btn-xs">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-base-content/60">No hay historias clinicas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $records->links() }}</div>
@endsection
