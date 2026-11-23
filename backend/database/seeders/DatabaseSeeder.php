<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['slug' => 'demo-zonewifi'],
            [
                'name' => 'ZoneWifi Démonstration',
                'country' => 'GN',
                'currency' => 'GNF',
                'is_active' => true,
            ]
        );

        $adminEmail = env('ZONEWIFI_ADMIN_EMAIL');
        $adminPassword = env('ZONEWIFI_ADMIN_PASSWORD');

        if (is_string($adminEmail) && $adminEmail !== ''
            && is_string($adminPassword) && strlen($adminPassword) >= 12) {
            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'company_id' => $company->id,
                    'name' => env('ZONEWIFI_ADMIN_NAME', 'Administrateur'),
                    'password' => Hash::make($adminPassword),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );
        }
    }
}
