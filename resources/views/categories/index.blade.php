@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Categorias</h1>
                <p class="page-subtitle">Organiza tus productos</p>
            </div>
            <div class="page-actions">
                <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm">Nueva</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Padre</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ $category->parent?->name ?? '-' }}</td>
                                <td class="text-right">
                                    <div class="actions">
                                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs" data-confirm="Eliminar categoria?">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-base-content/60">Sin registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $categories->links() }}
    </div>
@endsection
