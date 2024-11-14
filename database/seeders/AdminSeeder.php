<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Hash;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::firstOrCreate(['name' => 'Super Admin']);
        $companyRole = Role::firstOrCreate(['name' => 'Company Admin']);
        $admin= User::create(['name'=>'Super Admin',
                        'email'=>'superadmin@billbae.com',
                        'password'=>Hash::make('123456'),
                        'is_admin'=>1,
                        'is_active'=>1
                    ]);
        $admin->assignRole($adminRole);
        $company= User::create(['name'=>'Company Admin',
                        'email'=>'companyadmin@billbae.com',
                        'password'=>Hash::make('123456'),
                        'is_admin'=>2,
                        'is_active'=>1
                    ]);
        $company->assignRole($companyRole);

    }
}
