@extends('layouts.hotel')

@section('title', 'Tableau de bord')

@section('content')

{{-- En-tête page --}}
<div class="mb-6">
    <h1 class="font-heading text-2xl font-semibold text-primary">Tableau de bord</h1>
    <p class="text-sm text-[#8a7a6a] mt-0.5">
        {{ ucfirst(\Carbon\Carbon::now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}
    </p>
</div>

{{-- 4 cartes statistiques --}}
<div class="grid grid-cols-4 gap-4 mb-6">

    <x-stat-card label="Arrivées" :value="$stats['arrivals_today']" subtitle="aujourd'hui" color="emerald">
        <x-slot name="icon">
            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>
    </x-stat-card>

    <x-stat-card label="Départs" :value="$stats['departures_today']" subtitle="aujourd'hui" color="orange">
        <x-slot name="icon">
            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot>
    </x-stat-card>

    @php
    $occupancyRate = $stats['rooms_total'] > 0
    ? round(($stats['rooms_occupied'] / $stats['rooms_total']) * 100)
    : 0;
    @endphp
    <x-stat-card
        label="Occupation"
        value="{{ $occupancyRate }}%"
        subtitle="{{ $stats['rooms_occupied'] }} / {{ $stats['rooms_total'] }} chambres"
        color="blue">
        <x-slot name="icon">
            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
        </x-slot>
        <div class="mt-2 h-1 bg-accent/40 rounded-full overflow-hidden">
            <div class="h-full bg-blue-400 rounded-full transition-all" style="width: {{ $occupancyRate }}%"></div>
        </div>
    </x-stat-card>

    <x-stat-card label="Housekeeping" :value="$stats['rooms_cleaning']" subtitle="chambre à nettoyer" color="purple">
        <x-slot name="icon">
            <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
        </x-slot>
    </x-stat-card>

</div>

{{-- Panneau bas --}}
<div class="grid grid-cols-3 gap-4">

    {{-- Réservations du jour (2/3) --}}
    <div class="col-span-2 bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-secondary/20">
            <h2 class="font-heading font-semibold text-primary text-sm">Réservations du jour</h2>
            <a href="{{ route('bookings.index') }}"
                class="text-xs text-secondary hover:text-primary transition-colors flex items-center gap-1">
                Voir tout
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        @if($arrivalsToday->isEmpty() && $departuresToday->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-[#c4b8a8]">
            <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm">Aucune réservation aujourd'hui</p>
        </div>
        @else
        <div class="divide-y divide-secondary/10">
            @foreach($arrivalsToday as $booking)
            <div class="flex items-center gap-4 px-5 py-3 hover:bg-accent/20 transition-colors">
                <span class="w-16 text-center text-xs font-medium px-2 py-1 rounded-full bg-emerald-50 text-emerald-600">Arrivée</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-primary truncate">{{ $booking->customer->full_name }}</p>
                    <p class="text-xs text-primary/50">Chambre {{ $booking->room->number }} — {{ $booking->room->roomType->name }}</p>
                </div>
                <p class="text-xs text-primary/50 flex-shrink-0">{{ $booking->adults_count }} pers.</p>
                <span class="text-xs font-medium px-2 py-1 rounded-full
                                {{ $booking->status->value === 'confirmed' ? 'bg-blue-50 text-blue-600' : 'bg-yellow-50 text-yellow-600' }}">
                    {{ $booking->status->label() }}
                </span>
            </div>
            @endforeach
            @foreach($departuresToday as $booking)
            <div class="flex items-center gap-4 px-5 py-3 hover:bg-accent/20 transition-colors">
                <span class="w-16 text-center text-xs font-medium px-2 py-1 rounded-full bg-orange-50 text-orange-500">Départ</span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-primary truncate">{{ $booking->customer->full_name }}</p>
                    <p class="text-xs text-primary/50">Chambre {{ $booking->room->number }} — {{ $booking->room->roomType->name }}</p>
                </div>
                <p class="text-xs text-primary/50 flex-shrink-0">{{ $booking->adults_count }} pers.</p>
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-50 text-green-600">
                    {{ $booking->status->label() }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Statut chambres (1/3) --}}
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-secondary/20">
            <h2 class="font-heading font-semibold text-primary text-sm">Statut chambres</h2>
            <a href="{{ route('rooms.index') }}"
                class="text-xs text-secondary hover:text-primary transition-colors flex items-center gap-1">
                Gérer
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
        <div class="px-5 py-4 space-y-3">
            @php
            $statusRows = [
            ['label' => 'Disponibles', 'count' => $stats['rooms_available'], 'dot' => 'bg-green-400', 'bar' => 'bg-green-400'],
            ['label' => 'Occupées', 'count' => $stats['rooms_occupied'], 'dot' => 'bg-blue-400', 'bar' => 'bg-blue-400'],
            ['label' => 'En nettoyage', 'count' => $stats['rooms_cleaning'], 'dot' => 'bg-yellow-400', 'bar' => 'bg-yellow-400'],
            ['label' => 'Maintenance', 'count' => $stats['rooms_maintenance'], 'dot' => 'bg-orange-400', 'bar' => 'bg-orange-400'],
            ];
            @endphp
            @foreach($statusRows as $row)
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $row['dot'] }}"></span>
                <span class="flex-1 text-xs text-primary/60">{{ $row['label'] }}</span>
                <div class="w-20 h-1 bg-accent/40 rounded-full overflow-hidden">
                    @if($stats['rooms_total'] > 0)
                    <div class="h-full {{ $row['bar'] }} rounded-full"
                        style="width: {{ ($row['count'] / $stats['rooms_total']) * 100 }}%"></div>
                    @endif
                </div>
                <span class="w-4 text-right text-xs font-semibold text-primary">{{ $row['count'] }}</span>
            </div>
            @endforeach
            <div class="pt-3 border-t border-secondary/20 flex justify-between items-center">
                <span class="text-xs text-primary/50">Total chambres</span>
                <span class="text-sm font-semibold text-primary">{{ $stats['rooms_total'] }}</span>
            </div>
        </div>
    </div>

</div>

@endsection