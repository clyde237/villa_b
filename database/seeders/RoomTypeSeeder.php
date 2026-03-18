<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RoomType;
use App\Models\Tenant;

class RoomTypeSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Tenant::where('slug', 'villa-boutanga')->value('id');

        $types = [
            [
                'name'          => 'Chambre Standard',
                'code'          => 'STD',
                'description'   => 'Chambre confortable avec vue sur jardin',
                'base_capacity' => 2,
                'max_capacity'  => 3,
                'size_sqm'      => 25,
                'base_price'    => 4500000, // 45 000 FCFA en centimes
                'amenities'     => ['wifi', 'climatisation', 'tv', 'salle_de_bain'],
            ],
            [
                'name'          => 'Chambre Supérieure',
                'code'          => 'SUP',
                'description'   => 'Chambre spacieuse avec vue sur la cour',
                'base_capacity' => 2,
                'max_capacity'  => 3,
                'size_sqm'      => 35,
                'base_price'    => 6500000,
                'amenities'     => ['wifi', 'climatisation', 'tv', 'minibar', 'coffre_fort'],
            ],
            [
                'name'          => 'Suite Junior',
                'code'          => 'SJR',
                'description'   => 'Suite avec salon séparé',
                'base_capacity' => 2,
                'max_capacity'  => 4,
                'size_sqm'      => 55,
                'base_price'    => 10000000,
                'amenities'     => ['wifi', 'climatisation', 'tv', 'minibar', 'coffre_fort', 'baignoire'],
            ],
            [
                'name'          => 'Suite Présidentielle',
                'code'          => 'SPRES',
                'description'   => 'Notre suite la plus luxueuse',
                'base_capacity' => 2,
                'max_capacity'  => 4,
                'size_sqm'      => 90,
                'base_price'    => 18000000,
                'amenities'     => ['wifi', 'climatisation', 'tv', 'minibar', 'coffre_fort', 'jacuzzi', 'terrasse'],
            ],
        ];

        foreach ($types as $type) {
            RoomType::create(array_merge($type, [
                'tenant_id' => $tenantId,
                'is_active' => true,
            ]));
        }
    }
}