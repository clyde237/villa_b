@extends('layouts.hotel')

@section('title', 'Groupes')

@section('content')

{{-- En-tête --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="font-heading text-2xl font-semibold text-primary">Réservations Groupe</h1>
        <p class="text-sm text-primary/50 mt-0.5">{{ $stats['total'] }} dossier{{ $stats['total'] > 1 ? 's' : '' }} au total</p>
    </div>
    <a href="{{ route('groups.create') }}"
       class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nouveau groupe
    </a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-4 gap-3 mb-5">
    @php
        $statCards = [
            ['key' => 'total',     'label' => 'Total dossiers', 'icon' => 'folder',     'color' => 'text-primary',      'bg' => 'bg-accent/30'],
            ['key' => 'pending',   'label' => 'En attente',     'icon' => 'clock',      'color' => 'text-yellow-600',   'bg' => 'bg-yellow-50'],
            ['key' => 'confirmed', 'label' => 'Confirmés',      'icon' => 'check-circle','color' => 'text-blue-600',    'bg' => 'bg-blue-50'],
            ['key' => 'in_house',  'label' => 'En séjour',      'icon' => 'hotel',      'color' => 'text-green-600',    'bg' => 'bg-green-50'],
        ];
    @endphp
    @foreach($statCards as $card)
        <div class="bg-white rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg {{ $card['bg'] }} flex items-center justify-center flex-shrink-0">
                <i data-lucide="{{ $card['icon'] }}" class="w-4 h-4 {{ $card['color'] }}"></i>
            </div>
            <div>
                <p class="text-lg font-heading font-semibold text-primary leading-none">{{ $stats[$card['key']] }}</p>
                <p class="text-xs text-primary/50 mt-0.5">{{ $card['label'] }}</p>
            </div>
        </div>
    @endforeach
</div>

{{-- Barre outils --}}
<div class="flex items-center justify-between gap-4 mb-5">
    <div class="flex items-center gap-2">
        @php
            $filters = [
                ''          => 'Tous',
                'pending'   => 'En attente',
                'confirmed' => 'Confirmés',
                'in_house'  => 'En séjour',
                'completed' => 'Terminés',
                'cancelled' => 'Annulés',
            ];
        @endphp
        @foreach($filters as $value => $label)
            <a href="{{ route('groups.index', array_merge(request()->except('status', 'page'), $value ? ['status' => $value] : [])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                      {{ request('status', '') === $value
                          ? 'bg-primary text-white'
                          : 'bg-white text-primary/60 hover:text-primary border border-secondary/30' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <form method="GET" action="{{ route('groups.index') }}" class="relative">
        <input type="text" id="search-input" name="search"
               value="{{ request('search') }}"
               placeholder="Code, nom du groupe, contact..."
               autocomplete="off"
               class="pl-9 pr-4 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary placeholder-primary/30 outline-none focus:border-secondary w-64 transition-all">
        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($groups->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-primary/30">
            <i data-lucide="users" class="w-10 h-10 mb-3 opacity-40"></i>
            <p class="text-sm">Aucun dossier groupe trouvé</p>
        </div>
    @else
        <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-secondary/10 bg-accent/20">
            <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Code</div>
            <div class="col-span-3 text-xs font-semibold uppercase tracking-widest text-primary/40">Groupe</div>
            <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Contact</div>
            <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Période</div>
            <div class="col-span-1 text-xs font-semibold uppercase tracking-widest text-primary/40">Chambres</div>
            <div class="col-span-1 text-xs font-semibold uppercase tracking-widest text-primary/40">Statut</div>
            <div class="col-span-1"></div>
        </div>

        @foreach($groups as $group)
            @php
                $statusColors = [
                    'pending'   => 'bg-yellow-50 text-yellow-700 border-yellow-200',
                    'confirmed' => 'bg-blue-50 text-blue-700 border-blue-200',
                    'in_house'  => 'bg-green-50 text-green-700 border-green-200',
                    'completed' => 'bg-gray-50 text-gray-600 border-gray-200',
                    'cancelled' => 'bg-red-50 text-red-600 border-red-200',
                ];
                $sc = $statusColors[$group->status] ?? 'bg-secondary/10 text-primary/60 border-secondary/20';
                $eventLabels = [
                    'family'     => 'Famille',
                    'corporate'  => 'Corporate',
                    'wedding'    => 'Mariage',
                    'tour_group' => 'Tour groupe',
                ];
            @endphp
            <a href="{{ route('groups.show', $group) }}"
               class="grid grid-cols-12 gap-4 px-5 py-3.5 border-b border-secondary/10 hover:bg-accent/10 transition-colors items-center cursor-pointer">

                <div class="col-span-2">
                    <span class="text-sm font-mono font-medium text-primary">{{ $group->group_code }}</span>
                </div>

                <div class="col-span-3">
                    <p class="text-sm font-medium text-primary truncate">{{ $group->group_name }}</p>
                    @if($group->event_type)
                        <p class="text-xs text-primary/40">{{ $eventLabels[$group->event_type] ?? $group->event_type }}</p>
                    @endif
                </div>

                <div class="col-span-2">
                    <p class="text-xs text-primary/70 truncate">{{ $group->contactCustomer?->full_name ?? '—' }}</p>
                </div>

                <div class="col-span-2">
                    <p class="text-xs text-primary">
                        {{ $group->start_date->locale('fr')->isoFormat('D MMM') }}
                        → {{ $group->end_date->locale('fr')->isoFormat('D MMM YYYY') }}
                    </p>
                    <p class="text-xs text-primary/40">
                        {{ $group->start_date->diffInDays($group->end_date) }} nuit{{ $group->start_date->diffInDays($group->end_date) > 1 ? 's' : '' }}
                    </p>
                </div>

                <div class="col-span-1">
                    <p class="text-sm font-medium text-primary">{{ $group->bookings_count }}</p>
                </div>

                <div class="col-span-1">
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full border {{ $sc }} capitalize">
                        {{ ucfirst($group->status) }}
                    </span>
                </div>

                <div class="col-span-1 flex justify-end">
                    <i data-lucide="chevron-right" class="w-4 h-4 text-primary/30"></i>
                </div>
            </a>
        @endforeach
    @endif
</div>

@if($groups->hasPages())
    <div class="mt-4">{{ $groups->links() }}</div>
@endif

<script>
let searchTimer;
document.getElementById('search-input').addEventListener('input', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => this.closest('form').submit(), 400);
});
</script>

@endsection