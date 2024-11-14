<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GstTaxPercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxPercentages = [
            ['id' => 2, 'percentage' => '5', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'percentage' => '12', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'percentage' => '18', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'percentage' => '28', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('gst_tax_percentages')->insert($taxPercentages);
    }
}
