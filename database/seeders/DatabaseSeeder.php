<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TenantSeeder::class,    // 1. D'abord le tenant
            UserSeeder::class,      // 2. Puis les users (besoin du tenant)
            RoomTypeSeeder::class,  // 3. Les types de chambres
            RoomSeeder::class,      // 4. Les chambres physiques
            CustomerSeeder::class,  // 5. Les clients (Factory × 50)
            BookingSeeder::class,   // 6. Les réservations
        ]);
    }
}
