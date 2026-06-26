@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Pacientes</h1>
                <p class="page-subtitle">Gestiona fichas y consulta el historial optometrico</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar paciente" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('optometry.patients.create') }}" class="btn btn-primary btn-sm">Nuevo paciente</a>
            </div>
        </div>
    </div>

    <div class="panel mt-6">
        <div class="panel-body overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Documento</th>
                        <th>Telefono</th>
                        <th>Ultima historia</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($patients as $patient)
                        <tr>
                            <td>{{ $patient->name }}</td>
                            <td>{{ $patient->document ?? '-' }}</td>
                            <td>{{ $patient->phone ?? '-' }}</td>
                            <td>{{ optional($patient->clinicalRecords()->latest('examined_at')->first()?->examined_at)->format('d/m/Y H:i') ?? '-' }}</td>
                            <td class="text-right">
                                <div class="actions justify-end">
                                    <a href="{{ route('optometry.patients.show', $patient) }}" class="btn btn-outline-primary btn-xs">Ver</a>
                                    <a href="{{ route('optometry.patients.edit', $patient) }}" class="btn btn-outline btn-xs">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-base-content/60">No hay pacientes registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $patients->links() }}</div>
@endsection
