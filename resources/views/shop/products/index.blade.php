@extends('layouts.hotel')

@section('title', 'Articles Boutique')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-primary">Articles Boutique</h1>
            <p class="text-secondary mt-1">Gestion des articles culturels</p>
        </div>
        <a href="{{ route('shop.products.create') }}"
           class="bg-primary hover:bg-primary/90 text-white px-6 py-3 rounded-lg font-medium transition-colors">
            <i data-lucide="plus" class="w-4 h-4 inline mr-2"></i> Nouvel article
        </a>
    </div>

    @if ($message = session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i> {{ $message }}
        </div>
    @endif

    {{-- Barre outils --}}
    <div class="flex items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-2">
            {{-- Badge Toutes les catégories --}}
            <a href="{{ route('shop.products.index', request()->except(['category', 'page'])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                      {{ !request('category')
                          ? 'bg-primary text-white'
                          : 'bg-white text-primary/60 hover:text-primary border border-secondary/30' }}">
                Toutes
            </a>
            {{-- Badges par catégorie --}}
            @foreach ($categories as $cat)
                <a href="{{ route('shop.products.index', array_merge(request()->except(['category', 'page']), ['category' => $cat->id])) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                          {{ request('category') == $cat->id
                              ? 'bg-primary text-white'
                              : 'bg-white text-primary/60 hover:text-primary border border-secondary/30' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2">
            {{-- Badges statut --}}
            @php
                $statuses = [
                    '' => 'Tous',
                    'active' => 'Actif',
                    'inactive' => 'Inactif',
                ];
            @endphp
            @foreach($statuses as $value => $label)
                <a href="{{ route('shop.products.index', array_merge(request()->except(['status', 'page']), $value ? ['status' => $value] : [])) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                          {{ request('status', '') === $value
                              ? 'bg-primary text-white'
                              : 'bg-white text-primary/60 hover:text-primary border border-secondary/30' }}">
                    {{ $label }}
                </a>
            @endforeach

            {{-- Recherche --}}
            <form method="GET" action="{{ route('shop.products.index') }}" class="relative ml-4">
                <input type="hidden" name="category" value="{{ request('category') }}">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="text"
                       id="search-input"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, SKU..."
                       autocomplete="off"
                       class="pl-9 pr-4 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary placeholder-primary/30 outline-none focus:border-secondary w-64 transition-all">
                <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
            </form>
        </div>
    </div>

    <!-- Tableau des articles -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Article</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Catégorie</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Prix</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Stock</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Statut</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($products as $product)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-primary">{{ $product->name }}</p>
                                    <p class="text-secondary text-sm">{{ $product->sku }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-secondary">{{ $product->category->name }}</td>
                            <td class="px-6 py-4 font-medium text-primary">
                                {{ number_format($product->price / 100, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-6 py-4">
                                <span class="{{ $product->stock_quantity > $product->reorder_level ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }} px-3 py-1 rounded-full text-sm font-medium">
                                    {{ $product->stock_quantity }} unité(s)
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if ($product->is_active)
                                    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-sm font-medium">Actif</span>
                                @else
                                    <span class="bg-gray-50 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('shop.products.edit', $product) }}"
                                   class="text-primary hover:text-primary/70 mr-4 transition-colors">
                                    <i data-lucide="edit" class="w-4 h-4 inline"></i>
                                </a>
                                <form action="{{ route('shop.products.destroy', $product) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Êtes-vous sûr ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4 inline"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-secondary">
                                <i data-lucide="box" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                                <p>Aucun article trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $products->links() }}
    </div>
</div>
@endsection
