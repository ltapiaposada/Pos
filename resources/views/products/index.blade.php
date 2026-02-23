@extends('layouts.admin')

@section('content')
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Productos</h1>
                <p class="page-subtitle">Catalogo y precios de venta</p>
            </div>
            <div class="page-actions">
                <form method="GET" class="join">
                    <input name="q" value="{{ request('q') }}" placeholder="Buscar" class="input input-bordered join-item input-sm">
                    <button class="btn btn-outline btn-sm join-item">Buscar</button>
                </form>
                <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm w-full sm:w-auto">Nuevo</a>
            </div>
        </div>
    </div>

    <div class="mt-6 panel">
        <div class="panel-body">
            <div class="space-y-3 md:hidden">
                @forelse ($products as $product)
                    <article class="surface-muted rounded-2xl p-4">
                        <div class="flex items-start gap-3">
                            <img
                                src="{{ $product->image_url ?: asset('images/product-placeholder.svg') }}"
                                alt="{{ $product->name }}"
                                style="width: 48px; height: 48px; object-fit: cover; border-radius: 10px;"
                            >
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold">{{ $product->name }}</p>
                                <p class="text-xs text-base-content/60">{{ $product->sku }}</p>
                                <p class="text-xs text-base-content/60">{{ $product->category?->name ?? 'Sin categoria' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Precio</p>
                                <p class="font-semibold">${{ number_format($product->sale_price, 2) }}</p>
                            </div>
                            <div class="rounded-xl border border-base-300 bg-base-100 px-3 py-2">
                                <p class="text-xs text-base-content/60">Tipo</p>
                                <p class="capitalize">{{ $product->product_type ?? 'simple' }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                {{ $product->is_active ? 'Si' : 'No' }}
                            </span>
                            <span class="badge {{ $product->is_visible_ecommerce ? 'badge-info' : 'badge-ghost' }}">
                                {{ $product->is_visible_ecommerce ? 'Visible' : 'Oculto' }}
                            </span>
                        </div>
                        <div class="mt-3 actions">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-xs" data-confirm="Eliminar producto?">Eliminar</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-base-300 bg-base-100 p-5 text-center text-sm text-base-content/60">Sin registros</div>
                @endforelse
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>SKU</th>
                            <th>Tipo</th>
                            <th>Precio</th>
                            <th>Activo</th>
                            <th>E-commerce</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <img
                                            src="{{ $product->image_url ?: asset('images/product-placeholder.svg') }}"
                                            alt="{{ $product->name }}"
                                            style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px;"
                                        >
                                        <div>
                                            <div class="font-medium">{{ $product->name }}</div>
                                            <div class="text-xs text-base-content/60">{{ $product->category?->name ?? 'Sin categoria' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $product->sku }}</td>
                                <td>
                                    <div class="text-sm capitalize">{{ $product->product_type ?? 'simple' }}</div>
                                    @if ($product->parentProduct)
                                        <div class="text-xs text-base-content/60">Base: {{ $product->parentProduct->name }}</div>
                                    @endif
                                </td>
                                <td>${{ number_format($product->sale_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $product->is_active ? 'badge-success' : 'badge-danger' }}">
                                        {{ $product->is_active ? 'Si' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $product->is_visible_ecommerce ? 'badge-info' : 'badge-ghost' }}">
                                        {{ $product->is_visible_ecommerce ? 'Visible' : 'Oculto' }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="actions">
                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-xs">Editar</a>
                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-xs" data-confirm="Eliminar producto?">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/60">Sin registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection
