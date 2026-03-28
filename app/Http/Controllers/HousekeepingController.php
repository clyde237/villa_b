<?php

namespace App\Http\Controllers;

use App\Enums\RoomStatus;
use App\Models\HousekeepingAssignment;
use App\Models\HousekeepingTeam;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HousekeepingController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $user = Auth::user();
        $teamIds = $user->housekeepingTeams()->pluck('housekeeping_teams.id');

        $teams = HousekeepingTeam::with(['leader', 'members', 'activeAssignments.room'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $staff = User::query()
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereIn('role', ['housekeeping_leader', 'housekeeping_staff', 'housekeeping']);
            })
            ->orderBy('name')
            ->get();

        $dirtyRooms = Room::with(['roomType', 'activeHousekeepingAssignment.team'])
            ->where('status', RoomStatus::DIRTY)
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        $activeAssignments = HousekeepingAssignment::with(['room.roomType', 'team.leader', 'team.members'])
            ->whereIn('status', ['pending', 'in_progress', 'blocked'])
            ->latest('assigned_at')
            ->get();

        $completedToday = HousekeepingAssignment::with(['room.roomType', 'team'])
            ->where('status', 'completed')
            ->whereDate('completed_at', today())
            ->latest('completed_at')
            ->get();

        $myAssignments = HousekeepingAssignment::with(['room.roomType', 'team'])
            ->whereIn('housekeeping_team_id', $teamIds)
            ->whereIn('status', ['pending', 'in_progress', 'blocked'])
            ->latest('assigned_at')
            ->get();

        $housekeepingPipeline = Room::with(['roomType', 'activeHousekeepingAssignment.team'])
            ->whereIn('status', [
                RoomStatus::DIRTY,
                RoomStatus::CLEANING,
                RoomStatus::CLEAN,
                RoomStatus::INSPECTED,
            ])
            ->orderByRaw("CASE status
                WHEN 'dirty' THEN 1
                WHEN 'cleaning' THEN 2
                WHEN 'clean' THEN 3
                WHEN 'inspected' THEN 4
                ELSE 5 END")
            ->orderBy('floor')
            ->orderBy('number')
            ->get()
            ->groupBy(fn ($room) => $room->status->value);

        $stats = [
            'dirty_rooms' => $dirtyRooms->count(),
            'teams' => $teams->count(),
            'pending_assignments' => $activeAssignments->where('status', 'pending')->count(),
            'in_progress_assignments' => $activeAssignments->where('status', 'in_progress')->count(),
            'blocked_assignments' => $activeAssignments->where('status', 'blocked')->count(),
            'completed_today' => $completedToday->count(),
        ];

        return view('housekeeping.index', compact(
            'teams',
            'staff',
            'dirtyRooms',
            'activeAssignments',
            'completedToday',
            'myAssignments',
            'housekeepingPipeline',
            'stats'
        ));
    }

    public function storeTeam(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
            'leader_id' => ['nullable', 'exists:users,id'],
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $tenantId = Auth::user()->tenant_id
            ?? Tenant::where('slug', 'villa-boutanga')->value('id');

        $memberIds = collect($validated['member_ids'])->map(fn($id) => (int) $id);

        if (!empty($validated['leader_id']) && !$memberIds->contains((int) $validated['leader_id'])) {
            $memberIds->push((int) $validated['leader_id']);
        }

        $allowedStaffIds = User::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('role', ['housekeeping_leader', 'housekeeping_staff', 'housekeeping'])
            ->pluck('id');

        if ($memberIds->diff($allowedStaffIds)->isNotEmpty()) {
            return back()->withErrors([
                'team' => 'Tous les membres de l’équipe doivent appartenir au service housekeeping.'
            ]);
        }

        $team = HousekeepingTeam::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'code' => $validated['code'] ?? null,
            'leader_id' => $validated['leader_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
        ]);

        $team->members()->sync($memberIds->unique()->all());

        return redirect()->route('housekeeping.index')->with('success', 'Équipe de nettoyage créée.');
    }

    public function assignRooms(Request $request)
    {
        $validated = $request->validate([
            'housekeeping_team_id' => ['required', 'exists:housekeeping_teams,id'],
            'room_ids' => ['required', 'array', 'min:1'],
            'room_ids.*' => ['integer', 'exists:rooms,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $team = HousekeepingTeam::findOrFail($validated['housekeeping_team_id']);
        $rooms = Room::whereIn('id', $validated['room_ids'])->get();

        if ($rooms->contains(fn($room) => $room->status !== RoomStatus::DIRTY)) {
            return back()->withErrors([
                'assignment' => 'Seules les chambres sales peuvent être affectées à une équipe.'
            ]);
        }

        DB::transaction(function () use ($validated, $team, $rooms) {
            foreach ($rooms as $room) {
                $existing = $room->housekeepingAssignments()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->first();

                if ($existing) {
                    $existing->update([
                        'housekeeping_team_id' => $team->id,
                        'assigned_by' => Auth::id(),
                        'assigned_at' => now(),
                        'notes' => $validated['notes'] ?? $existing->notes,
                        'status' => 'pending',
                        'started_at' => null,
                        'completed_at' => null,
                    ]);

                    continue;
                }

                HousekeepingAssignment::create([
                    'tenant_id' => $room->tenant_id,
                    'housekeeping_team_id' => $team->id,
                    'room_id' => $room->id,
                    'assigned_by' => Auth::id(),
                    'status' => 'pending',
                    'notes' => $validated['notes'] ?? null,
                    'assigned_at' => now(),
                ]);
            }
        });

        return redirect()->route('housekeeping.index')->with('success', 'Chambres affectées à l’équipe.');
    }

    public function reportIssue(Request $request, Room $room)
    {
        $validated = $request->validate([
            'issue_notes' => ['required', 'string', 'max:1000'],
            'mark_as_maintenance' => ['nullable', 'boolean'],
        ]);

        $assignment = $room->activeHousekeepingAssignment;

        if (!$assignment) {
            return back()->withErrors(['status' => 'Aucune affectation active trouvée pour cette chambre.']);
        }

        DB::transaction(function () use ($room, $assignment, $validated) {
            $assignment->update([
                'status' => 'blocked',
                'issue_notes' => $validated['issue_notes'],
                'reported_by' => Auth::id(),
                'reported_at' => now(),
                'started_at' => $assignment->started_at ?? now(),
            ]);

            if ($validated['mark_as_maintenance'] ?? false) {
                $room->updateStatus(RoomStatus::MAINTENANCE, 'Problème signalé par housekeeping', Auth::id());
            }
        });

        return redirect()->route('housekeeping.index')->with('success', 'Le problème a été signalé au chef de service.');
    }

    public function markCleaning(Request $request, Room $room)
    {
        $assignment = $room->activeHousekeepingAssignment;

        if (!$assignment) {
            return back()->withErrors(['status' => 'Cette chambre n’a pas encore été affectée à une équipe.']);
        }

        if (!$room->status->canTransitionTo(RoomStatus::CLEANING)) {
            return back()->withErrors(['status' => 'Cette chambre ne peut pas être mise en nettoyage.']);
        }

        DB::transaction(function () use ($room, $assignment) {
            $room->updateStatus(RoomStatus::CLEANING, 'Nettoyage démarré', Auth::id());

            $assignment->update([
                'status' => 'in_progress',
                'started_at' => $assignment->started_at ?? now(),
            ]);
        });

        return back()->with('success', "Chambre {$room->number} en cours de nettoyage.");
    }

    public function markReady(Request $request, Room $room)
    {
        $assignment = $room->activeHousekeepingAssignment;

        if (!$assignment) {
            return back()->withErrors(['status' => 'Aucune affectation active trouvée pour cette chambre.']);
        }

        if (!$room->status->canTransitionTo(RoomStatus::CLEAN)) {
            return back()->withErrors(['status' => 'Cette chambre ne peut pas être marquée propre.']);
        }

        DB::transaction(function () use ($room, $assignment) {
            $room->updateStatus(RoomStatus::CLEAN, 'Nettoyage terminé', Auth::id());

            $assignment->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        });

        return back()->with('success', "Chambre {$room->number} marquée propre.");
    }
}
