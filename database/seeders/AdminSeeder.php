<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('users')->insert([
            'name' => "Admin",
            'email' => env('ADMIN_USER', 'admin@admin.com'),
            'password' => bcrypt(env('ADMIN_PASS', 'password')),
            'dni' => '',
            'isadmin' => true,
            'phone' => ''
        ]);
    }
}
