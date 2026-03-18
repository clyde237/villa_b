<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Customer;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Toutes ces requêtes sont automatiquement filtrées par tenant_id
        // grâce au BelongsToTenant global scope — on n'a rien à faire

        $stats = [
            // Occupation en temps réel
            'rooms_total'       => Room::count(),
            'rooms_available'   => Room::where('status', RoomStatus::AVAILABLE)->count(),
            'rooms_occupied'    => Room::where('status', RoomStatus::OCCUPIED)->count(),
            'rooms_cleaning'    => Room::where('status', RoomStatus::CLEANING)->count(),
            'rooms_maintenance' => Room::where('status', RoomStatus::MAINTENANCE)->count(),

            // Activité du jour
            'arrivals_today'    => Booking::arrivingToday()->count(),
            'departures_today'  => Booking::departingToday()->count(),
            'in_house'          => Booking::inHouse()->count(),

            // Clients
            'customers_total'   => Customer::count(),
        ];

        // Arrivées d'aujourd'hui avec les infos nécessaires
        $arrivalsToday = Booking::arrivingToday()
            ->with(['customer', 'room.roomType']) // eager loading — évite le N+1
            ->orderBy('check_in')
            ->get();

        // Départs d'aujourd'hui
        $departuresToday = Booking::departingToday()
            ->with(['customer', 'room.roomType'])
            ->orderBy('check_out')
            ->get();

        // Chambres qui nécessitent une attention
        $roomsNeedingAttention = Room::whereIn('status', [
            RoomStatus::CLEANING,
            RoomStatus::MAINTENANCE,
            RoomStatus::OUT_OF_ORDER,
        ])->with('roomType')->get();

        return view('dashboard', compact(
            'stats',
            'arrivalsToday',
            'departuresToday',
            'roomsNeedingAttention'
        ));
    }
}