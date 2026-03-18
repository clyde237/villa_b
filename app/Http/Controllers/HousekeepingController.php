<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Http\Request;

class HousekeepingController extends Controller
{
    public function index()
    {
        // Vue housekeeping : toutes les chambres groupées par statut
        $rooms = Room::with('roomType')
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy(fn($room) => $room->status->value);

        return view('housekeeping.index', compact('rooms'));
    }

    public function markCleaning(Request $request, Room $room)
    {
        if (!$room->status->canTransitionTo(RoomStatus::CLEANING)) {
            return back()->withErrors(['status' => 'Cette chambre ne peut pas être mise en nettoyage.']);
        }

        $room->updateStatus(RoomStatus::CLEANING, 'Nettoyage démarré');

        return back()->with('success', "Chambre {$room->number} en cours de nettoyage.");
    }

    public function markReady(Request $request, Room $room)
    {
        if (!$room->status->canTransitionTo(RoomStatus::AVAILABLE)) {
            return back()->withErrors(['status' => 'Cette chambre ne peut pas être marquée disponible.']);
        }

        $room->updateStatus(RoomStatus::AVAILABLE, 'Nettoyage terminé');

        return back()->with('success', "Chambre {$room->number} disponible.");
    }
}