<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        DB::table('users')->insert([
			'name' => 'Daniel Ximenez',
			'email' => 'daniximenez@cev.com',
			'password' => Hash::make('password'),
			'biography' => 'Esta es la biografia de un usuario, solo es una prueba.',
			'salary' => 50000,
			'role' => 'directive',
]);
    }
}
