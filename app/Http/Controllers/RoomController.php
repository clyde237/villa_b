<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy('floor'); // Grouper par étage pour l'affichage

        return view('rooms.index', compact('rooms'));
    }

    public function show(Room $room)
    {
        // Laravel injecte automatiquement l'objet Room via Route Model Binding
        // Si l'ID n'existe pas → 404 automatique
        $room->load([
            'roomType',
            'statusHistory' => fn($q) => $q->orderBy('changed_at', 'desc')->limit(10),
            'bookings'      => fn($q) => $q->orderBy('check_in', 'desc')->limit(5),
        ]);

        return view('rooms.show', compact('room'));
    }

    public function updateStatus(Request $request, Room $room)
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_column(RoomStatus::cases(), 'value'))],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $newStatus = RoomStatus::from($validated['status']);

        // Vérifie que la transition est autorisée via notre Enum
        if (!$room->status->canTransitionTo($newStatus)) {
            return back()->withErrors([
                'status' => "Transition impossible : {$room->status->label()} → {$newStatus->label()}"
            ]);
        }

        $room->updateStatus($newStatus, $validated['reason'] ?? null);

        return back()->with('success', "Statut mis à jour : {$newStatus->label()}");
    }
}