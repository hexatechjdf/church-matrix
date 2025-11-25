<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

            User::create([
                'name'          => 'Super Admin',
                'email'          => 'superadmin@gmail.com',
                'password'   => bcrypt('12345678'),
                'role'           => 0,
            ]);

            User::create([
                'name'          => 'Company Admin',
                'email'          => 'user@gmail.com',
                'password'   => bcrypt('12345678'),
                'role'           => 1,
                
            ]);

    }
}
