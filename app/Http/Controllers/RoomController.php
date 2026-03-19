<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $tab    = $request->get('tab', 'rooms');
        $view   = $request->get('view', 'list');
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        // --- Données onglet Chambres ---
        $roomsQuery = Room::with('roomType')->orderBy('floor')->orderBy('number');

        if ($status !== 'all') {
            $roomsQuery->where('status', $status);
        }

        if ($search) {
            $roomsQuery->where(function ($q) use ($search) {
                $q->where('number', 'ilike', "%{$search}%")
                    ->orWhereHas('roomType', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
            });
        }

        $rooms = $roomsQuery->paginate(20)->withQueryString();

        // Compteurs pour les filtres
        $counts = [
            'all'           => Room::count(),
            'available'     => Room::where('status', RoomStatus::AVAILABLE)->count(),
            'occupied'      => Room::where('status', RoomStatus::OCCUPIED)->count(),
            'out_of_order'  => Room::where('status', RoomStatus::OUT_OF_ORDER)->count(),
            'maintenance'   => Room::where('status', RoomStatus::MAINTENANCE)->count(),
        ];

        // --- Données onglet Types ---
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->get();

        return view('rooms.index', compact(
            'rooms',
            'roomTypes',
            'counts',
            'tab',
            'view',
            'status',
            'search'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'exists:room_types,id'],
            'number'       => ['required', 'string', 'max:20'],
            'floor'        => ['nullable', 'string', 'max:10'],
            'view_type'    => ['nullable', 'string', 'max:50'],
            'notes'        => ['nullable', 'string'],
        ]);

        $validated['tenant_id'] = Auth::user()->tenant_id
            ?? \App\Models\Tenant::where('slug', 'villa-boutanga')->value('id');

        Room::create($validated);

        return redirect()->route('rooms.index', ['tab' => 'rooms'])
            ->with('success', 'Chambre créée avec succès.');
    }

    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'exists:room_types,id'],
            'number'       => ['required', 'string', 'max:20'],
            'floor'        => ['nullable', 'string', 'max:10'],
            'view_type'    => ['nullable', 'string', 'max:50'],
            'notes'        => ['nullable', 'string'],
        ]);

        $room->update($validated);

        return redirect()->route('rooms.index', ['tab' => 'rooms'])
            ->with('success', 'Chambre mise à jour.');
    }

    public function destroy(Room $room)
    {
        // Vérifie qu'aucune réservation active n'est liée
        if ($room->bookings()->whereNotIn('status', ['cancelled', 'completed'])->exists()) {
            return back()->withErrors(['delete' => 'Impossible de supprimer une chambre avec des réservations actives.']);
        }

        $room->delete();

        return redirect()->route('rooms.index', ['tab' => 'rooms'])
            ->with('success', 'Chambre supprimée.');
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = RoomStatus::from($validated['status']);

        if (!$room->status->canTransitionTo($newStatus)) {
            return back()->withErrors([
                'status' => "Transition impossible : {$room->status->label()} → {$newStatus->label()}"
            ]);
        }

        $room->updateStatus($newStatus, $validated['reason'] ?? null);

        return back()->with('success', "Statut mis à jour : {$newStatus->label()}");
    }

    // --- Types de chambres ---

    public function storeType(Request $request)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
            'base_capacity' => ['required', 'integer', 'min:1'],
            'max_capacity'  => ['required', 'integer', 'min:1'],
            'base_price'    => ['required', 'integer', 'min:0'],
            'size_sqm'      => ['nullable', 'integer'],
        ]);

        // base_price : l'utilisateur saisit en FCFA, on stocke en centimes
        $validated['base_price'] = $validated['base_price'] * 100;

        $validated['tenant_id'] = Auth::user()->tenant_id
            ?? \App\Models\Tenant::where('slug', 'villa-boutanga')->value('id');

        RoomType::create($validated);

        return redirect()->route('rooms.index', ['tab' => 'types'])
            ->with('success', 'Type de chambre créé.');
    }

    public function updateType(Request $request, RoomType $roomType)
    {
        $validated = $request->validate([
            'name'          => ['required', 'string', 'max:100'],
            'code'          => ['required', 'string', 'max:50'],
            'description'   => ['nullable', 'string'],
            'base_capacity' => ['required', 'integer', 'min:1'],
            'max_capacity'  => ['required', 'integer', 'min:1'],
            'base_price'    => ['required', 'integer', 'min:0'],
            'size_sqm'      => ['nullable', 'integer'],
        ]);

        $validated['base_price'] = $validated['base_price'] * 100;

        $roomType->update($validated);

        return redirect()->route('rooms.index', ['tab' => 'types'])
            ->with('success', 'Type mis à jour.');
    }

    public function destroyType(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return back()->withErrors(['delete' => 'Impossible de supprimer un type avec des chambres associées.']);
        }

        $roomType->delete();

        return redirect()->route('rooms.index', ['tab' => 'types'])
            ->with('success', 'Type supprimé.');
    }

    public function show(Room $room)
    {
        $room->load(['roomType', 'statusHistory' => fn($q) => $q->orderBy('changed_at', 'desc')->limit(10)]);
        return view('rooms.show', compact('room'));
    }
}
