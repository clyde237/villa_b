<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Tenant;

class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::where('slug', 'villa-boutanga')->value('id');

        $std  = RoomType::where('code', 'STD')->value('id');
        $sup  = RoomType::where('code', 'SUP')->value('id');
        $sjr  = RoomType::where('code', 'SJR')->value('id');
        $spres = RoomType::where('code', 'SPRES')->value('id');

        $rooms = [
            // Étage 1 — Standards
            ['number' => '101', 'floor' => '1', 'view_type' => 'garden',   'room_type_id' => $std],
            ['number' => '102', 'floor' => '1', 'view_type' => 'garden',   'room_type_id' => $std],
            ['number' => '103', 'floor' => '1', 'view_type' => 'courtyard','room_type_id' => $std],
            // Étage 1 — Supérieures
            ['number' => '104', 'floor' => '1', 'view_type' => 'pool',     'room_type_id' => $sup],
            ['number' => '105', 'floor' => '1', 'view_type' => 'pool',     'room_type_id' => $sup],
            // Étage 2 — Standards
            ['number' => '201', 'floor' => '2', 'view_type' => 'garden',   'room_type_id' => $std],
            ['number' => '202', 'floor' => '2', 'view_type' => 'garden',   'room_type_id' => $std],
            // Étage 2 — Supérieures
            ['number' => '203', 'floor' => '2', 'view_type' => 'heritage', 'room_type_id' => $sup],
            // Étage 2 — Suite Junior
            ['number' => '204', 'floor' => '2', 'view_type' => 'pool',     'room_type_id' => $sjr],
            // Étage 3 — Suite Présidentielle
            ['number' => '301', 'floor' => '3', 'view_type' => 'heritage', 'room_type_id' => $spres],
        ];

        foreach ($rooms as $room) {
            Room::create(array_merge($room, [
                'tenant_id' => $tenantId,
                'status'    => 'available',
                'is_active' => true,
            ]));
        }
    }
}