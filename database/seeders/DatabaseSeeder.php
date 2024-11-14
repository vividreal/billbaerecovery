<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            RolesTableSeeder::class,
            PermissionsTableSeeder::class,
            BusinessTypesSeeder::class,
            CountriesTableSeeder::class,
            ShopStatesSeeder::class,
            DistrictSeeder::class,
            TimezoneSeeder::class,
            CurrenciesTableSeeder::class



        ]);
       
       
    }
}
