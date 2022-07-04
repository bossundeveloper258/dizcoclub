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
            'name' => "Admin Dvergara",
            'email' => 'dvergara10@gmail.com',
            'password' => bcrypt('UKH^7%b2^e'),
            'dni' => '',
            'isadmin' => true,
            'phone' => ''
        ]);

        DB::table('users')->insert([
            'name' => "Admin Codesigners",
            'email' => 'codesignersperu@gmail.com',
            'password' => bcrypt('Nss@b93w3<'),
            'dni' => '',
            'isadmin' => true,
            'phone' => ''
        ]);
    }
}
