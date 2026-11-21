<?php
namespace Database\Seeders;
use App\Models\Company;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        Company::firstOrCreate(['slug'=>'demo-zonewifi'], ['name'=>'ZoneWifi Démonstration','country'=>'GN','currency'=>'GNF','is_active'=>true]);
    }
}
