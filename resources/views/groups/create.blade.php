@extends('layouts.hotel')

@section('title', 'Nouveau groupe')

@section('content')

<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('groups.index') }}"
           class="text-xs text-primary/50 hover:text-primary transition-colors flex items-center gap-1 mb-2">
            <i data-lucide="arrow-left" class="w-3 h-3"></i>
            Retour aux groupes
        </a>
        <h1 class="font-heading text-2xl font-semibold text-primary">Nouveau dossier groupe</h1>
        <p class="text-sm text-primary/50 mt-0.5">Étape 1 — Informations du groupe</p>
    </div>

    @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Recherche contact --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <h2 class="font-heading font-semibold text-primary mb-4">Contact principal du groupe</h2>

        <form method="GET" action="{{ route('groups.create') }}" class="relative mb-4">
            <input type="text" id="search-input" name="search"
                   value="{{ request('search') }}"
                   placeholder="Rechercher un client existant..."
                   autocomplete="off"
                   class="w-full pl-10 pr-4 py-2.5 text-sm border border-secondary/30 rounded-lg text-primary placeholder-primary/30 outline-none focus:border-secondary">
            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
        </form>

        @if($customers->isNotEmpty())
            <div class="space-y-2 mb-4">
                @foreach($customers as $c)
                    <button type="button"
                            onclick="selectContact({{ $c->id }}, '{{ $c->full_name }}', '{{ $c->phone ?? '' }}')"
                            class="w-full flex items-center gap-3 p-3 rounded-lg border border-secondary/20 hover:border-secondary/50 hover:bg-accent/10 transition-colors text-left">
                        <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center flex-shrink-0">
                            <span class="text-white text-xs font-semibold">
                                {{ strtoupper(substr($c->first_name, 0, 1) . substr($c->last_name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-primary">{{ $c->full_name }}</p>
                            <p class="text-xs text-primary/50">{{ $c->phone ?? $c->email ?? '—' }}</p>
                        </div>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Contact sélectionné --}}
        <div id="selected-contact" class="hidden bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
                <span id="selected-contact-name" class="text-sm font-medium text-primary"></span>
            </div>
            <button type="button" onclick="clearContact()" class="text-xs text-primary/50 hover:text-primary">Changer</button>
        </div>
    </div>

    {{-- Formulaire groupe --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="font-heading font-semibold text-primary mb-5">Détails du groupe</h2>

        <form method="POST" action="{{ route('groups.store') }}">
            @csrf
            <input type="hidden" name="contact_customer_id" id="contact_customer_id" value="{{ old('contact_customer_id') }}">

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                        Nom du groupe *
                    </label>
                    <input type="text" name="group_name"
                           value="{{ old('group_name') }}"
                           placeholder="Ex: Famille Nkomo, Séminaire UNESCO..."
                           required
                           class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary placeholder-primary/30">
                    @error('group_name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                        Type d'événement *
                    </label>
                    <select name="event_type" required
                            class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                        <option value="">Sélectionner...</option>
                        <option value="family"     {{ old('event_type') === 'family'     ? 'selected' : '' }}>Famille</option>
                        <option value="corporate"  {{ old('event_type') === 'corporate'  ? 'selected' : '' }}>Corporate / Séminaire</option>
                        <option value="wedding"    {{ old('event_type') === 'wedding'    ? 'selected' : '' }}>Mariage</option>
                        <option value="tour_group" {{ old('event_type') === 'tour_group' ? 'selected' : '' }}>Tour groupe</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                        Date d'arrivée *
                    </label>
                    <input type="date" name="start_date"
                           value="{{ old('start_date') }}"
                           min="{{ now()->format('Y-m-d') }}"
                           required
                           class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    @error('start_date')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                        Date de départ *
                    </label>
                    <input type="date" name="end_date"
                           value="{{ old('end_date') }}"
                           required
                           class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary">
                    @error('end_date')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-semibold uppercase tracking-widest text-primary/50 mb-1.5">
                    Notes
                </label>
                <textarea name="notes" rows="3"
                          placeholder="Besoins spéciaux, programme, remarques..."
                          class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-lg text-primary outline-none focus:border-secondary resize-none placeholder-primary/30">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('groups.index') }}"
                   class="px-4 py-2 text-sm text-primary/60 hover:text-primary transition-colors">
                    Annuler
                </a>
                <button type="submit" id="submit-btn" disabled
                        class="px-5 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                    Créer le dossier groupe
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectContact(id, name, phone) {
    document.getElementById('contact_customer_id').value = id;
    document.getElementById('selected-contact-name').textContent = name + (phone ? ' — ' + phone : '');
    document.getElementById('selected-contact').classList.remove('hidden');
    document.getElementById('submit-btn').disabled = false;
}

function clearContact() {
    document.getElementById('contact_customer_id').value = '';
    document.getElementById('selected-contact').classList.add('hidden');
    document.getElementById('submit-btn').disabled = true;
}

let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => this.closest('form').submit(), 400);
});

// Si contact déjà sélectionné (retour avec erreur)
@if(old('contact_customer_id'))
    document.getElementById('submit-btn').disabled = false;
    document.getElementById('selected-contact').classList.remove('hidden');
    document.getElementById('selected-contact-name').textContent = 'Contact sélectionné (ID: {{ old("contact_customer_id") }})';
@endif
</script>

@endsection