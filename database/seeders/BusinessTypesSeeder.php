<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('business_types')->insert([
            ['id' => 1, 'name' => 'Hotel spa', 'status' => 1, 'created_at' => '2021-04-13 02:56:45', 'updated_at' => '2021-04-13 02:56:45'],
            ['id' => 2, 'name' => 'Day spa', 'status' => 1, 'created_at' => '2021-04-13 02:57:15', 'updated_at' => '2021-04-13 02:57:15'],
            ['id' => 3, 'name' => 'Beauty saloon & spa', 'status' => 1, 'created_at' => '2021-04-13 03:00:31', 'updated_at' => '2021-04-13 03:16:37'],
            ['id' => 4, 'name' => 'Tattoo Studios', 'status' => 1, 'created_at' => '2021-04-13 03:22:24', 'updated_at' => '2021-04-13 03:22:35'],
            ['id' => 5, 'name' => 'Nail Spa', 'status' => 1, 'created_at' => '2021-04-15 04:30:42', 'updated_at' => '2021-04-15 04:30:42'],
            ['id' => 6, 'name' => 'Fish Spa', 'status' => 1, 'created_at' => '2021-04-15 04:30:55', 'updated_at' => '2021-04-15 04:30:55'],
        ]);
    }
}
