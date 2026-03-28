@extends('layouts.hotel')

@section('title', 'Housekeeping')

@section('content')

@php
    $pipelineLabels = [
        'dirty' => 'Sales',
        'cleaning' => 'En nettoyage',
        'clean' => 'Propres',
        'inspected' => 'Contrôlées',
    ];
@endphp

<div class="flex flex-col gap-2 mb-6">
    <h1 class="font-heading text-2xl font-semibold text-primary">Housekeeping</h1>
    <p class="text-sm text-primary/50">Vue opérationnelle du service ménage, avec pilotage chef de service et suivi terrain sur mobile.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
    {{ $errors->first() }}
</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4 mb-6">
    <x-stat-card label="Sales" :value="$stats['dirty_rooms']" subtitle="à affecter" color="red" />
    <x-stat-card label="Equipes" :value="$stats['teams']" subtitle="actives" color="blue" />
    <x-stat-card label="En attente" :value="$stats['pending_assignments']" subtitle="assignations" color="orange" />
    <x-stat-card label="En cours" :value="$stats['in_progress_assignments']" subtitle="nettoyages" color="purple" />
    <x-stat-card label="Bloquées" :value="$stats['blocked_assignments']" subtitle="problèmes signalés" color="orange" />
    <x-stat-card label="Terminées" :value="$stats['completed_today']" subtitle="aujourd'hui" color="emerald" />
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden xl:col-span-2">
        <div class="px-5 py-4 border-b border-secondary/20 flex items-center justify-between">
            <h2 class="font-heading font-semibold text-primary text-sm">Mes chambres assignées</h2>
            <span class="text-xs text-primary/40">{{ $myAssignments->count() }} tâche{{ $myAssignments->count() > 1 ? 's' : '' }}</span>
        </div>

        @if($myAssignments->isEmpty())
        <div class="px-5 py-10 text-sm text-primary/40 text-center">
            Aucune chambre ne t'est affectée pour le moment.
        </div>
        @else
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($myAssignments as $assignment)
            <div class="rounded-2xl border border-secondary/20 bg-accent/10 p-4">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="font-heading font-semibold text-primary text-lg">Chambre {{ $assignment->room->number }}</p>
                        <p class="text-xs text-primary/45">{{ $assignment->room->roomType->name }} · Equipe {{ $assignment->team->name }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium
                        {{ $assignment->status === 'pending' ? 'bg-orange-50 text-orange-700' : '' }}
                        {{ $assignment->status === 'in_progress' ? 'bg-blue-50 text-blue-700' : '' }}
                        {{ $assignment->status === 'blocked' ? 'bg-red-50 text-red-700' : '' }}">
                        {{ $assignment->status === 'pending' ? 'A faire' : ($assignment->status === 'in_progress' ? 'En cours' : 'Problème') }}
                    </span>
                </div>

                <div class="space-y-2 mb-4">
                    <p class="text-xs text-primary/60">
                        Statut chambre :
                        <span class="font-medium text-primary">{{ $assignment->room->status->label() }}</span>
                    </p>
                    @if($assignment->notes)
                    <p class="text-xs text-primary/50 italic">{{ $assignment->notes }}</p>
                    @endif
                    @if($assignment->issue_notes)
                    <div class="rounded-lg bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700">
                        {{ $assignment->issue_notes }}
                    </div>
                    @endif
                </div>

                <div class="space-y-2">
                    @if($assignment->status === 'pending')
                    <form method="POST" action="{{ route('housekeeping.clean', $assignment->room) }}">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold bg-yellow-500 text-white hover:bg-yellow-600 transition-colors">
                            Démarrer le nettoyage
                        </button>
                    </form>
                    @endif

                    @if(in_array($assignment->status, ['pending', 'in_progress']))
                    <form method="POST" action="{{ route('housekeeping.ready', $assignment->room) }}">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                            Valider le nettoyage
                        </button>
                    </form>

                    <form method="POST" action="{{ route('housekeeping.issue', $assignment->room) }}" class="space-y-2">
                        @csrf
                        <textarea
                            name="issue_notes"
                            rows="3"
                            required
                            class="w-full px-3 py-2 text-sm border border-secondary/30 rounded-xl bg-white text-primary focus:outline-none focus:border-secondary resize-none"
                            placeholder="Ex: fuite d'eau, draps manquants, odeur persistante..."></textarea>
                        <label class="flex items-center gap-2 text-xs text-primary/60">
                            <input type="checkbox" name="mark_as_maintenance" value="1" class="rounded border-secondary/40 text-primary focus:ring-primary">
                            Basculer la chambre en maintenance
                        </label>
                        <button type="submit" class="w-full py-3 rounded-xl text-sm font-semibold bg-red-600 text-white hover:bg-red-700 transition-colors">
                            Signaler un problème
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-secondary/20">
            <h2 class="font-heading font-semibold text-primary text-sm">Pipeline housekeeping</h2>
            <p class="text-xs text-primary/40 mt-1">Seuls les statuts ménage sont affichés ici.</p>
        </div>

        <div class="p-4 space-y-4">
            @foreach($pipelineLabels as $status => $label)
            <div class="rounded-xl border border-secondary/20 p-3">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-primary">{{ $label }}</p>
                    <span class="text-xs text-primary/40">{{ $housekeepingPipeline->get($status, collect())->count() }}</span>
                </div>

                @if($housekeepingPipeline->get($status, collect())->isEmpty())
                <p class="text-xs text-primary/35">Aucune chambre.</p>
                @else
                <div class="space-y-2">
                    @foreach($housekeepingPipeline->get($status, collect()) as $room)
                    <div class="flex items-center justify-between gap-3 text-xs">
                        <div>
                            <p class="font-medium text-primary">Chambre {{ $room->number }}</p>
                            <p class="text-primary/40">{{ $room->roomType->name }}</p>
                        </div>
                        @if($room->activeHousekeepingAssignment)
                        <span class="px-2 py-1 rounded-full bg-secondary/10 text-primary/70">{{ $room->activeHousekeepingAssignment->team->name }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
    @role('housekeeping_leader', 'manager')
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-semibold text-primary text-sm">Créer une équipe</h2>
            <span class="text-xs text-primary/40 uppercase tracking-widest">Chef de service</span>
        </div>

        <form method="POST" action="{{ route('housekeeping.teams.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-primary/50 mb-1.5">Nom</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary">
                </div>
                <div>
                    <label class="block text-xs text-primary/50 mb-1.5">Code</label>
                    <input type="text" name="code" placeholder="HK-1" class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary">
                </div>
            </div>

            <div>
                <label class="block text-xs text-primary/50 mb-1.5">Chef d'équipe</label>
                <select name="leader_id" class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary">
                    <option value="">Aucun chef désigné</option>
                    @foreach($staff as $member)
                    <option value="{{ $member->id }}">{{ $member->name }} · {{ $member->role }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-primary/50 mb-2">Membres terrain</label>
                <div class="max-h-48 overflow-y-auto rounded-lg border border-secondary/20 divide-y divide-secondary/10">
                    @forelse($staff as $member)
                    <label class="flex items-center gap-3 px-3 py-2 text-xs text-primary">
                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="rounded border-secondary/40 text-primary focus:ring-primary">
                        <span class="flex-1">{{ $member->name }}</span>
                        <span class="text-primary/40">{{ $member->role }}</span>
                    </label>
                    @empty
                    <div class="px-3 py-4 text-xs text-primary/40">Aucun agent housekeeping disponible.</div>
                    @endforelse
                </div>
            </div>

            <div>
                <label class="block text-xs text-primary/50 mb-1.5">Notes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary resize-none" placeholder="Zone, étage, spécialité..."></textarea>
            </div>

            <button type="submit" class="w-full py-2 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-surface-dark transition-colors">
                Enregistrer l'équipe
            </button>
        </form>
    </div>
    @endrole

    <div class="bg-white rounded-xl shadow-sm p-5 xl:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="font-heading font-semibold text-primary text-sm">Affecter les chambres sales</h2>
                <p class="text-xs text-primary/40 mt-1">Le chef de service affecte une ou plusieurs chambres sales à une équipe.</p>
            </div>
            <span class="text-xs text-primary/40">{{ $dirtyRooms->count() }} chambre{{ $dirtyRooms->count() > 1 ? 's' : '' }}</span>
        </div>

        @role('housekeeping_leader', 'manager')
        @if($dirtyRooms->isEmpty())
        <div class="rounded-xl border border-dashed border-secondary/30 px-4 py-10 text-sm text-primary/40 text-center">
            Aucune chambre sale à affecter.
        </div>
        @elseif($teams->isEmpty())
        <div class="rounded-xl border border-dashed border-secondary/30 px-4 py-10 text-sm text-primary/40 text-center">
            Crée d'abord une équipe de nettoyage.
        </div>
        @else
        <form method="POST" action="{{ route('housekeeping.assignments.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs text-primary/50 mb-1.5">Equipe</label>
                <select name="housekeeping_team_id" required class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary">
                    <option value="">Sélectionner une équipe</option>
                    @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}{{ $team->leader ? ' · ' . $team->leader->name : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs text-primary/50">Chambres sales</label>
                    <button type="button" onclick="toggleDirtyRooms(true)" class="text-[11px] text-primary/50 hover:text-primary">Tout cocher</button>
                </div>
                <div class="max-h-64 overflow-y-auto rounded-lg border border-secondary/20 divide-y divide-secondary/10">
                    @foreach($dirtyRooms as $room)
                    <label class="flex items-center gap-3 px-3 py-2 text-xs text-primary">
                        <input type="checkbox" name="room_ids[]" value="{{ $room->id }}" class="dirty-room-checkbox rounded border-secondary/40 text-primary focus:ring-primary">
                        <span class="font-medium">{{ $room->number }}</span>
                        <span class="text-primary/40">{{ $room->roomType->name }}</span>
                        @if($room->activeHousekeepingAssignment)
                        <span class="ml-auto text-[11px] text-orange-600">déjà affectée</span>
                        @endif
                    </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block text-xs text-primary/50 mb-1.5">Consignes</label>
                <textarea name="notes" rows="3" class="w-full px-3 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary focus:outline-none focus:border-secondary resize-none" placeholder="Priorité, étage, consignes..."></textarea>
            </div>

            <button type="submit" class="w-full py-2 rounded-lg text-xs font-semibold bg-primary text-white hover:bg-surface-dark transition-colors">
                Affecter la sélection
            </button>
        </form>
        @endif
        @else
        <div class="rounded-xl border border-dashed border-secondary/30 px-4 py-10 text-sm text-primary/40 text-center">
            L'affectation des chambres est réservée au chef de service housekeeping.
        </div>
        @endrole
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-secondary/20 flex items-center justify-between">
            <h2 class="font-heading font-semibold text-primary text-sm">Equipes de nettoyage</h2>
            <span class="text-xs text-primary/40">{{ $teams->count() }} équipe{{ $teams->count() > 1 ? 's' : '' }}</span>
        </div>

        @if($teams->isEmpty())
        <div class="px-5 py-10 text-sm text-primary/40 text-center">Aucune équipe créée pour le moment.</div>
        @else
        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($teams as $team)
            <div class="rounded-xl border border-secondary/20 p-4 bg-accent/10">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="font-heading font-semibold text-primary">{{ $team->name }}</p>
                        <p class="text-xs text-primary/40">{{ $team->code ?: 'Sans code' }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-secondary/10 text-primary/70">
                        {{ $team->activeAssignments->count() }} chambre{{ $team->activeAssignments->count() > 1 ? 's' : '' }}
                    </span>
                </div>
                <p class="text-xs text-primary/60 mb-2">Chef : <span class="font-medium text-primary">{{ $team->leader?->name ?? 'Non défini' }}</span></p>
                <div class="flex flex-wrap gap-2">
                    @foreach($team->members as $member)
                    <span class="px-2 py-1 rounded-full bg-white border border-secondary/20 text-xs text-primary/70">{{ $member->name }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-secondary/20 flex items-center justify-between">
            <h2 class="font-heading font-semibold text-primary text-sm">Problèmes et nettoyages terminés</h2>
            <span class="text-xs text-primary/40">{{ $completedToday->count() }} terminé{{ $completedToday->count() > 1 ? 's' : '' }}</span>
        </div>

        <div class="divide-y divide-secondary/10">
            @forelse($activeAssignments->where('status', 'blocked') as $assignment)
            <div class="px-5 py-4">
                <div class="flex items-center justify-between gap-3 mb-1">
                    <p class="text-sm font-medium text-primary">Chambre {{ $assignment->room->number }} · {{ $assignment->team->name }}</p>
                    <span class="px-2 py-1 rounded-full bg-red-50 text-red-700 text-xs font-medium">Problème</span>
                </div>
                <p class="text-xs text-red-700">{{ $assignment->issue_notes }}</p>
            </div>
            @empty
            <div class="px-5 py-4 text-xs text-primary/35">Aucun problème signalé.</div>
            @endforelse

            @forelse($completedToday as $assignment)
            <div class="px-5 py-4">
                <div class="flex items-center justify-between gap-3 mb-1">
                    <p class="text-sm font-medium text-primary">Chambre {{ $assignment->room->number }} · {{ $assignment->team->name }}</p>
                    <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-medium">Propre</span>
                </div>
                <p class="text-xs text-primary/45">{{ optional($assignment->completed_at)->locale('fr')->diffForHumans() }}</p>
            </div>
            @empty
            <div class="px-5 py-4 text-xs text-primary/35">Aucun nettoyage terminé aujourd'hui.</div>
            @endforelse
        </div>
    </div>
</div>

<script>
function toggleDirtyRooms(checked) {
    document.querySelectorAll('.dirty-room-checkbox').forEach((checkbox) => {
        checkbox.checked = checked;
    });
}
</script>

@endsection
