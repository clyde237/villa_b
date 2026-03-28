@extends('layouts.hotel')

@section('title', 'Chambres')

@section('content')

{{-- En-tête --}}
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="font-heading text-2xl font-semibold text-primary">Chambres</h1>
        <p class="text-sm text-primary/50 mt-0.5">
            {{ $counts['all'] }} chambre{{ $counts['all'] > 1 ? 's' : '' }} ·
            {{ $roomTypes->count() }} type{{ $roomTypes->count() > 1 ? 's' : '' }}
        </p>
    </div>
    @if($tab === 'rooms')
    @role('manager')
    <button onclick="document.getElementById('modal-create-room').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nouvelle chambre
    </button>
    @endrole
    @else
    @role('manager')
    <button onclick="document.getElementById('modal-create-type').classList.remove('hidden')"
        class="flex items-center gap-2 px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Nouveau type
    </button>
    @endrole
    @endif
</div>

{{-- Messages flash --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg">
    {{ session('success') }}
</div>
@endif
@if($errors->has('delete'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
    {{ $errors->first('delete') }}
</div>
@endif

{{-- Onglets --}}
<div class="flex items-center gap-6 border-b border-secondary/20 mb-5">
    <a href="{{ route('rooms.index', array_merge(request()->query(), ['tab' => 'rooms'])) }}"
        class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 transition-colors
              {{ $tab === 'rooms' ? 'border-primary text-primary' : 'border-transparent text-primary/40 hover:text-primary/70' }}">
        <i data-lucide="hotel" class="w-4 h-4"></i>
        Chambres
    </a>
    <a href="{{ route('rooms.index', array_merge(request()->query(), ['tab' => 'types'])) }}"
        class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 transition-colors
              {{ $tab === 'types' ? 'border-primary text-primary' : 'border-transparent text-primary/40 hover:text-primary/70' }}">
        <i data-lucide="layers" class="w-4 h-4"></i>
        Types de chambre
    </a>
</div>

{{-- ===== ONGLET CHAMBRES ===== --}}
@if($tab === 'rooms')

{{-- Barre outils --}}
<div class="flex items-center justify-between gap-4 mb-4">
    <div class="flex items-center gap-2 flex-wrap">
        @php
        $filters = [
        'all' => 'Tous',
        'available' => 'Disponibles',
        'occupied' => 'Occupées',
        'out_of_order' => 'Hors service',
        'maintenance' => 'Maintenance',
        ];
        @endphp
        @foreach($filters as $value => $label)
        <a href="{{ route('rooms.index', array_merge(request()->query(), ['tab' => 'rooms', 'status' => $value])) }}"
            class="px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                          {{ $status === $value
                              ? 'bg-primary text-white'
                              : 'bg-white text-primary/60 hover:text-primary border border-secondary/30' }}">
            {{ $label }}
            <span class="ml-1 opacity-70">({{ $counts[$value] }})</span>
        </a>
        @endforeach
    </div>

    <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('rooms.index') }}" class="relative">
            <input type="hidden" name="tab" value="rooms">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="view" value="{{ $view }}">
            <input type="text"
                name="search"
                id="search-input"
                value="{{ $search }}"
                placeholder="Rechercher une chambre..."
                class="pl-9 pr-4 py-2 text-xs border border-secondary/30 rounded-lg bg-white text-primary placeholder-primary/30 outline-none focus:border-secondary w-52 transition-all"
                autocomplete="off">
            <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-primary/30"></i>
        </form>

        <div class="flex items-center bg-white border border-secondary/30 rounded-lg overflow-hidden">
            <a href="{{ route('rooms.index', array_merge(request()->query(), ['view' => 'list'])) }}"
                class="px-3 py-2 transition-colors {{ $view === 'list' ? 'bg-primary text-white' : 'text-primary/40 hover:text-primary' }}">
                <i data-lucide="list" class="w-4 h-4"></i>
            </a>
            <a href="{{ route('rooms.index', array_merge(request()->query(), ['view' => 'card'])) }}"
                class="px-3 py-2 transition-colors {{ $view === 'card' ? 'bg-primary text-white' : 'text-primary/40 hover:text-primary' }}">
                <i data-lucide="layout-grid" class="w-4 h-4"></i>
            </a>
        </div>
    </div>
</div>

{{-- Vue Liste --}}
@if($view === 'list')
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($rooms->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-primary/30">
        <i data-lucide="door-open" class="w-10 h-10 mb-3 opacity-40"></i>
        <p class="text-sm">Aucune chambre trouvée</p>
    </div>
    @else
    <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-secondary/10 bg-accent/20">
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Chambre</div>
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Type</div>
        <div class="col-span-3 text-xs font-semibold uppercase tracking-widest text-primary/40">Étage / Vue</div>
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Statut</div>
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Housekeeping</div>
        <div class="col-span-1"></div>
    </div>

    @foreach($rooms as $room)
    @php
    $statusColors = [
    'available' => 'bg-green-50 text-green-700 border-green-200',
    'occupied' => 'bg-blue-50 text-blue-700 border-blue-200',
    'cleaning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
    'maintenance' => 'bg-orange-50 text-orange-700 border-orange-200',
    'out_of_order' => 'bg-red-50 text-red-700 border-red-200',
    ];
    $colorClass = $statusColors[$room->status->value] ?? 'bg-secondary/10 text-primary/60 border-secondary/30';
    @endphp
    <div class="grid grid-cols-12 gap-4 px-5 py-3.5 border-b border-secondary/10 hover:bg-accent/10 transition-colors items-center">
        <div class="col-span-2 flex items-center gap-2">
            <i data-lucide="door-open" class="w-4 h-4 text-primary/30 flex-shrink-0"></i>
            <span class="text-sm font-semibold text-primary">{{ $room->number }}</span>
        </div>
        <div class="col-span-2 text-sm text-primary/70">{{ $room->roomType->name }}</div>
        <div class="col-span-3 flex items-center gap-2 text-sm text-primary/70">
            <span>{{ $room->floor ?? '—' }}</span>
            @if($room->view_type)
            <span class="text-primary/30">·</span>
            <span class="capitalize">{{ $room->view_type }}</span>
            @endif
        </div>
        <div class="col-span-2">
            <span class="px-2.5 py-1 text-xs font-medium rounded-full border {{ $colorClass }}">
                {{ $room->status->label() }}
            </span>
        </div>
        <div class="col-span-2">
            @if($room->activeHousekeepingAssignment)
            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-50 text-yellow-700 border border-yellow-200">
                {{ $room->activeHousekeepingAssignment->team->name }}
            </span>
            @elseif($room->status->value === 'dirty')
            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-50 text-red-700 border border-red-200">
                à affecter
            </span>
            @elseif(in_array($room->status->value, ['clean', 'inspected', 'available']))
            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                prêt
            </span>
            @else
            <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-secondary/10 text-primary/60 border border-secondary/20">
                suivi manuel
            </span>
            @endif
        </div>
        <div class="col-span-1 flex items-center justify-end gap-1">
            <a href="{{ route('rooms.show', $room) }}"
                class="p-1.5 text-primary/30 hover:text-primary transition-colors rounded"
                title="Voir détail">
                <i data-lucide="settings" class="w-4 h-4"></i>
            </a>
            @role('manager')
            <button
                data-id="{{ $room->id }}"
                data-number="{{ $room->number }}"
                data-type="{{ $room->room_type_id }}"
                data-floor="{{ $room->floor }}"
                data-view="{{ $room->view_type }}"
                onclick="openEditRoom(this)"
                class="p-1.5 text-primary/30 hover:text-primary transition-colors rounded"
                title="Modifier">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                onsubmit="return confirm('Supprimer la chambre {{ $room->number }} ?')"
                class="expect-popup">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 text-primary/30 hover:text-red-500 transition-colors rounded" title="Supprimer">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
            @endrole
        </div>
    </div>
    @endforeach
    @endif
</div>

@if($rooms->hasPages())
<div class="mt-4">{{ $rooms->links() }}</div>
@endif

{{-- Vue Carte --}}
@else
@if($rooms->isEmpty())
<div class="flex flex-col items-center justify-center py-16 text-primary/30">
    <p class="text-sm">Aucune chambre trouvée</p>
</div>
@else
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @foreach($rooms as $room)
    @php
    $cardColors = [
    'available' => ['border' => 'border-green-200', 'dot' => 'bg-green-400', 'badge' => 'bg-green-50 text-green-700'],
    'occupied' => ['border' => 'border-blue-200', 'dot' => 'bg-blue-400', 'badge' => 'bg-blue-50 text-blue-700'],
    'cleaning' => ['border' => 'border-yellow-200', 'dot' => 'bg-yellow-400', 'badge' => 'bg-yellow-50 text-yellow-700'],
    'maintenance' => ['border' => 'border-orange-200', 'dot' => 'bg-orange-400', 'badge' => 'bg-orange-50 text-orange-700'],
    'out_of_order' => ['border' => 'border-red-200', 'dot' => 'bg-red-400', 'badge' => 'bg-red-50 text-red-700'],
    ];
    $c = $cardColors[$room->status->value] ?? ['border' => 'border-secondary/20', 'dot' => 'bg-secondary', 'badge' => 'bg-secondary/10 text-primary/60'];
    @endphp
    <div class="bg-white rounded-xl border {{ $c['border'] }} p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full {{ $c['dot'] }}"></span>
                    <span class="font-heading font-semibold text-primary text-lg">{{ $room->number }}</span>
                </div>
                <p class="text-xs text-primary/50 mt-0.5">{{ $room->roomType->name }}</p>
            </div>
            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $c['badge'] }}">
                {{ $room->status->label() }}
            </span>
        </div>

        <div class="space-y-1 mb-4">
            @if($room->floor)
            <div class="flex items-center gap-1.5 text-xs text-primary/50">
                <i data-lucide="building-2" class="w-3 h-3"></i>
                Étage {{ $room->floor }}
            </div>
            @endif
            @if($room->view_type)
            <div class="flex items-center gap-1.5 text-xs text-primary/50 capitalize">
                <i data-lucide="eye" class="w-3 h-3"></i>
                Vue {{ $room->view_type }}
            </div>
            @endif
            <div class="flex items-center gap-1.5 text-xs text-primary/50">
                <i data-lucide="users" class="w-3 h-3"></i>
                {{ $room->roomType->base_capacity }} pers.
            </div>
        </div>

        <div class="flex items-center justify-end gap-1 pt-3 border-t border-secondary/10">
            <a href="{{ route('rooms.show', $room) }}"
                class="p-1.5 text-primary/30 hover:text-primary transition-colors rounded">
                <i data-lucide="settings" class="w-3.5 h-3.5"></i>
            </a>
            <button
                data-id="{{ $room->id }}"
                data-number="{{ $room->number }}"
                data-type="{{ $room->room_type_id }}"
                data-floor="{{ $room->floor }}"
                data-view="{{ $room->view_type }}"
                onclick="openEditRoom(this)"
                class="p-1.5 text-primary/30 hover:text-primary transition-colors rounded">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i>
            </button>
            <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                onsubmit="return confirm('Supprimer la chambre {{ $room->number }} ?')"
                class="expect-popup">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 text-primary/30 hover:text-red-500 transition-colors rounded">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

@if($rooms->hasPages())
<div class="mt-4">{{ $rooms->links() }}</div>
@endif
@endif
@endif

{{-- ===== ONGLET TYPES ===== --}}
@else
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @if($roomTypes->isEmpty())
    <div class="flex flex-col items-center justify-center py-16 text-primary/30">
        <p class="text-sm">Aucun type de chambre</p>
    </div>
    @else
    <div class="grid grid-cols-12 gap-4 px-5 py-3 border-b border-secondary/10 bg-accent/20">
        <div class="col-span-3 text-xs font-semibold uppercase tracking-widest text-primary/40">Nom</div>
        <div class="col-span-4 text-xs font-semibold uppercase tracking-widest text-primary/40">Description</div>
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Capacité</div>
        <div class="col-span-2 text-xs font-semibold uppercase tracking-widest text-primary/40">Nb chambres</div>
        <div class="col-span-1"></div>
    </div>

    @foreach($roomTypes as $type)
    <div class="grid grid-cols-12 gap-4 px-5 py-3.5 border-b border-secondary/10 hover:bg-accent/10 transition-colors items-center">
        <div class="col-span-3 font-medium text-sm text-primary">{{ $type->name }}</div>
        <div class="col-span-4 text-sm text-primary/50">{{ $type->description ?? '—' }}</div>
        <div class="col-span-2 text-sm text-primary/70">{{ $type->base_capacity }} pers.</div>
        <div class="col-span-2 text-sm text-primary/70">{{ $type->rooms_count }}</div>
        <div class="col-span-1 flex items-center justify-end gap-1">
            {{-- ✅ data-* attributes au lieu de paramètres JS inline --}}
            <button
                data-id="{{ $type->id }}"
                data-name="{{ $type->name }}"
                data-code="{{ $type->code }}"
                data-desc="{{ $type->description }}"
                data-base-cap="{{ $type->base_capacity }}"
                data-max-cap="{{ $type->max_capacity }}"
                data-price="{{ $type->base_price / 100 }}"
                data-sqm="{{ $type->size_sqm ?? 0 }}"
                onclick="openEditType(this)"
                class="p-1.5 text-primary/30 hover:text-primary transition-colors rounded">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
            <form method="POST" action="{{ route('rooms.types.destroy', $type) }}"
                onsubmit="return confirm('Supprimer le type {{ $type->name }} ?')"
                class="expect-popup">
                @csrf @method('DELETE')
                <button type="submit" class="p-1.5 text-primary/30 hover:text-red-500 transition-colors rounded">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endif


{{-- ===================================================== --}}
{{-- MODALS                                                --}}
{{-- ===================================================== --}}

<style>
    .modal-backdrop {
        background: rgba(15, 2, 1, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-input {
        width: 100%;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        border: 1px solid rgba(204, 171, 135, 0.3);
        border-radius: 0.5rem;
        color: #391F0E;
        outline: none;
        transition: border-color 0.15s;
        background: white;
    }

    .modal-input:focus {
        border-color: #CCAB87;
    }

    .modal-label {
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(57, 31, 14, 0.5);
        margin-bottom: 0.35rem;
    }
</style>

{{-- Modal : Créer chambre --}}
<div id="modal-create-room" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary/20">
            <h3 class="font-heading font-semibold text-primary">Nouvelle chambre</h3>
            <button onclick="document.getElementById('modal-create-room').classList.add('hidden')"
                class="text-primary/30 hover:text-primary transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('rooms.store') }}" class="px-6 py-5 space-y-4 expect-popup">
            @csrf
            <div>
                <label class="modal-label">Type de chambre *</label>
                <select name="room_type_id" required class="modal-input">
                    <option value="">Sélectionner...</option>
                    @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Numéro *</label>
                    <input type="text" name="number" placeholder="101" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Étage</label>
                    <input type="text" name="floor" placeholder="1" class="modal-input">
                </div>
            </div>
            <div>
                <label class="modal-label">Vue</label>
                <select name="view_type" class="modal-input">
                    <option value="">Aucune</option>
                    <option value="garden">Garden</option>
                    <option value="pool">Pool</option>
                    <option value="heritage">Heritage</option>
                    <option value="courtyard">Courtyard</option>
                    <option value="city">City</option>
                </select>
            </div>
            <div>
                <label class="modal-label">Notes internes</label>
                <textarea name="notes" rows="2" placeholder="Notes optionnelles..." class="modal-input resize-none"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-create-room').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-primary/60 hover:text-primary transition-colors">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
                    Créer la chambre
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal : Éditer chambre --}}
<div id="modal-edit-room" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary/20">
            <h3 class="font-heading font-semibold text-primary">Modifier la chambre</h3>
            <button onclick="document.getElementById('modal-edit-room').classList.add('hidden')"
                class="text-primary/30 hover:text-primary transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="form-edit-room" method="POST" action="" class="px-6 py-5 space-y-4 expect-popup">
            @csrf @method('PUT')
            <div>
                <label class="modal-label">Type de chambre *</label>
                <select id="edit-room-type" name="room_type_id" required class="modal-input">
                    @foreach($roomTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Numéro *</label>
                    <input type="text" id="edit-room-number" name="number" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Étage</label>
                    <input type="text" id="edit-room-floor" name="floor" class="modal-input">
                </div>
            </div>
            <div>
                <label class="modal-label">Vue</label>
                <select id="edit-room-view" name="view_type" class="modal-input">
                    <option value="">Aucune</option>
                    <option value="garden">Garden</option>
                    <option value="pool">Pool</option>
                    <option value="heritage">Heritage</option>
                    <option value="courtyard">Courtyard</option>
                    <option value="city">City</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-room').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-primary/60 hover:text-primary transition-colors">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal : Créer type --}}
<div id="modal-create-type" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary/20">
            <h3 class="font-heading font-semibold text-primary">Nouveau type de chambre</h3>
            <button onclick="document.getElementById('modal-create-type').classList.add('hidden')"
                class="text-primary/30 hover:text-primary transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('rooms.types.store') }}" class="px-6 py-5 space-y-4 expect-popup">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Nom *</label>
                    <input type="text" name="name" placeholder="Standard" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Code *</label>
                    <input type="text" name="code" placeholder="STD" required class="modal-input">
                </div>
            </div>
            <div>
                <label class="modal-label">Description</label>
                <textarea name="description" rows="2" class="modal-input resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Capacité base *</label>
                    <input type="number" name="base_capacity" value="2" min="1" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Capacité max *</label>
                    <input type="number" name="max_capacity" value="3" min="1" required class="modal-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Prix / nuit (FCFA) *</label>
                    <input type="number" name="base_price" placeholder="45000" min="0" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Surface (m²)</label>
                    <input type="number" name="size_sqm" placeholder="25" min="0" class="modal-input">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-create-type').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-primary/60 hover:text-primary transition-colors">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
                    Créer le type
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal : Éditer type --}}
<div id="modal-edit-type" class="hidden fixed inset-0 z-50 flex items-center justify-center modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-secondary/20">
            <h3 class="font-heading font-semibold text-primary">Modifier le type</h3>
            <button onclick="document.getElementById('modal-edit-type').classList.add('hidden')"
                class="text-primary/30 hover:text-primary transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form id="form-edit-type" method="POST" action="" class="px-6 py-5 space-y-4 expect-popup">
            @csrf @method('PUT')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Nom *</label>
                    <input type="text" id="edit-type-name" name="name" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Code *</label>
                    <input type="text" id="edit-type-code" name="code" required class="modal-input">
                </div>
            </div>
            <div>
                <label class="modal-label">Description</label>
                <textarea id="edit-type-desc" name="description" rows="2" class="modal-input resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Capacité base *</label>
                    <input type="number" id="edit-type-base-cap" name="base_capacity" min="1" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Capacité max *</label>
                    <input type="number" id="edit-type-max-cap" name="max_capacity" min="1" required class="modal-input">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="modal-label">Prix / nuit (FCFA) *</label>
                    <input type="number" id="edit-type-price" name="base_price" min="0" required class="modal-input">
                </div>
                <div>
                    <label class="modal-label">Surface (m²)</label>
                    <input type="number" id="edit-type-sqm" name="size_sqm" min="0" class="modal-input">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-type').classList.add('hidden')"
                    class="px-4 py-2 text-sm text-primary/60 hover:text-primary transition-colors">Annuler</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-surface-dark transition-colors">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Scripts --}}
<script>
    // ✅ Utilise this (le bouton) au lieu de paramètres inline
    // → Évite les bugs avec les apostrophes dans les chaînes
    function openEditRoom(btn) {
        document.getElementById('form-edit-room').action = `/rooms/${btn.dataset.id}`;
        document.getElementById('edit-room-number').value = btn.dataset.number;
        document.getElementById('edit-room-floor').value = btn.dataset.floor || '';
        document.getElementById('edit-room-type').value = btn.dataset.type;
        document.getElementById('edit-room-view').value = btn.dataset.view || '';
        document.getElementById('modal-edit-room').classList.remove('hidden');
    }

    function openEditType(btn) {
        document.getElementById('form-edit-type').action = `/rooms/types/${btn.dataset.id}`;
        document.getElementById('edit-type-name').value = btn.dataset.name;
        document.getElementById('edit-type-code').value = btn.dataset.code;
        document.getElementById('edit-type-desc').value = btn.dataset.desc || '';
        document.getElementById('edit-type-base-cap').value = btn.dataset.baseCap;
        document.getElementById('edit-type-max-cap').value = btn.dataset.maxCap;
        document.getElementById('edit-type-price').value = btn.dataset.price;
        document.getElementById('edit-type-sqm').value = btn.dataset.sqm || '';
        document.getElementById('modal-edit-type').classList.remove('hidden');
    }

    // Recherche à la saisie avec debounce 400ms
    let searchTimer;
    document.getElementById('search-input').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            this.closest('form').submit();
        }, 400);
    });

    // Fermer modal en cliquant sur le backdrop
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
    });
</script>

@endsection
