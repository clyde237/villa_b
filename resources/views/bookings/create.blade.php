@extends('layouts.hotel')

@section('title', 'Nouvelle réservation')

@section('content')

<div class="max-w-2xl mx-auto">

    {{-- En-tête --}}
    <div class="mb-6">
        <a href="{{ route('bookings.index') }}"
           class="text-xs text-primary/50 hover:text-primary transition-colors flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-3 h-3"></i>
            Retour aux réservations
        </a>
        <h1 class="font-heading text-2xl font-semibold text-primary">Nouvelle réservation</h1>
        <p class="text-sm text-primary/50 mt-0.5">Étape 1 — Sélection du client</p>
    </div>

    {{-- Indicateur d'étapes --}}
    <div class="flex items-center gap-3 mb-8">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center text-xs font-semibold">1</div>
            <span class="text-xs font-medium text-primary">Client</span>
        </div>
        <div class="flex-1 h-px bg-secondary/20"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-secondary/20 text-primary/40 flex items-center justify-center text-xs font-semibold">2</div>
            <span class="text-xs text-primary/40">Chambre & dates</span>
        </div>
        <div class="flex-1 h-px bg-secondary/20"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-secondary/20 text-primary/40 flex items-center justify-center text-xs font-semibold">3</div>
            <span class="text-xs text-primary/40">Confirmation</span>
        </div>
    </div>

    {{-- Client déjà sélectionné --}}
    @if($customer)
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center">
                    <span class="text-white text-sm font-semibold">
                        {{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm font-medium text-primary">{{ $customer->full_name }}</p>
                    <p class="text-xs text-primary/50">
                        {{ $customer->loyalty_level }} · {{ number_format($customer->loyalty_points) }} pts
                    </p>
                </div>
            </div>
            <a href="{{ route('bookings.create') }}" class="text-xs text-primary/50 hover:text-primary">Changer</a>
        </div>

        {{-- Étape 2 : Dates et personnes --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-heading font-semibold text-primary mb-5">Dates et personnes</h2>
            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <input type="hidden" name="step" value="2">
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                            Arrivée *
                        </label>
                        <input type="date" name="check_in"
                               min="{{ now()->format('Y-m-d') }}"
                               value="{{ old('check_in') }}"
                               required
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        @error('check_in')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                            Départ *
                        </label>
                        <input type="date" name="check_out"
                               value="{{ old('check_out') }}"
                               required
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        @error('check_out')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                            Adultes *
                        </label>
                        <input type="number" name="adults" value="{{ old('adults', 1) }}"
                               min="1" required
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                            Enfants
                        </label>
                        <input type="number" name="children" value="{{ old('children', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                        Origine
                    </label>
                    <select name="source"
                            class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        <option value="direct">Direct</option>
                        <option value="phone">Téléphone</option>
                        <option value="email">Email</option>
                        <option value="walk_in">Walk-in</option>
                        <option value="ota_bookingcom">Booking.com</option>
                    </select>
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors flex items-center justify-center gap-2">
                    Voir les chambres disponibles
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>

    {{-- Pas encore de client sélectionné --}}
    @else

        {{-- Recherche client existant --}}
        <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
            <h2 class="font-heading font-semibold text-primary mb-4">Rechercher un client existant</h2>

            <form method="GET" action="{{ route('bookings.create') }}" class="relative mb-4">
                <input type="text"
                       id="search-input"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, email, téléphone..."
                       autocomplete="off"
                       class="w-full pl-10 pr-4 py-2.5 text-sm border border-secondary/30 rounded-lg text-primary placeholder-primary/30 outline-none focus:border-secondary">
                <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
            </form>

            @if($customers->isNotEmpty())
                <div class="space-y-2">
                    @foreach($customers as $c)
                        <form method="POST" action="{{ route('bookings.store') }}">
                            @csrf
                            <input type="hidden" name="step" value="1">
                            <input type="hidden" name="customer_id" value="{{ $c->id }}">
                            <button type="submit"
                                    class="w-full flex items-center gap-3 p-3 rounded-lg border border-secondary/20 hover:border-secondary/50 hover:bg-accent/10 transition-colors text-left">
                                <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                                    <span class="text-white text-xs font-semibold">
                                        {{ strtoupper(substr($c->first_name, 0, 1) . substr($c->last_name, 0, 1)) }}
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-primary">{{ $c->full_name }}</p>
                                    <p class="text-xs text-primary/50">{{ $c->email ?? $c->phone ?? '—' }}</p>
                                </div>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-secondary/10 text-primary/60 capitalize">
                                    {{ $c->loyalty_level }}
                                </span>
                            </button>
                        </form>
                    @endforeach
                </div>
            @elseif(request()->filled('search'))
                <p class="text-sm text-primary/40 text-center py-4">Aucun client trouvé pour "{{ request('search') }}"</p>
            @endif
        </div>

        {{-- Séparateur --}}
        <div class="flex items-center gap-4 mb-4">
            <div class="flex-1 h-px bg-secondary/20"></div>
            <span class="text-xs text-primary/40 font-medium">ou créer un nouveau client</span>
            <div class="flex-1 h-px bg-secondary/20"></div>
        </div>

        {{-- Formulaire nouveau client --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="font-heading font-semibold text-primary mb-4">Nouveau client</h2>

            <form method="POST" action="{{ route('bookings.store') }}">
                @csrf
                <input type="hidden" name="step" value="1">
                <input type="hidden" name="new_customer" value="1">

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Prénom *</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Nom *</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Nationalité</label>
                        <input type="text" name="nationality" placeholder="CM" value="{{ old('nationality') }}" maxlength="5"
                               class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">Type document</label>
                        <select name="id_document_type"
                                class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                            <option value="">Sélectionner...</option>
                            <option value="passport">Passeport</option>
                            <option value="id_card">Carte d'identité</option>
                            <option value="driver_license">Permis de conduire</option>
                        </select>
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors flex items-center justify-center gap-2">
                    Créer le client et continuer
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    @endif
</div>

<script>
let searchTimer;
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => this.closest('form').submit(), 400);
    });
}
</script>

@endsection