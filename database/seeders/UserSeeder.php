<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'villa-boutanga')->first();

        // Super admin — pas de tenant (cross-établissements)
        User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@villaboutanga.cm',
            'password'  => Hash::make('password'),
            'tenant_id' => null,
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // Directeur de l'établissement
        User::create([
            'name'      => 'Jean-Pierre Kamga',
            'email'     => 'manager@villaboutanga.cm',
            'password'  => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role'      => 'manager',
            'is_active' => true,
        ]);

        // Réceptionniste
        User::create([
            'name'      => 'Marie Tchoupo',
            'email'     => 'reception@villaboutanga.cm',
            'password'  => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role'      => 'reception',
            'is_active' => true,
        ]);

        // Housekeeping
        User::create([
            'name'      => 'Paul Nguemo',
            'email'     => 'housekeeping@villaboutanga.cm',
            'password'  => Hash::make('password'),
            'tenant_id' => $tenant->id,
            'role'      => 'housekeeping',
            'is_active' => true,
        ]);
    }
}