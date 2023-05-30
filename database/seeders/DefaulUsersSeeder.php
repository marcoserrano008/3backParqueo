<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DefaulUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([

            [
                'name' => 'guardia',
                'email' => 'guardia@guardia.com',
                'apellido_paterno' => 'apellido',
                'apellido_materno' => 'apellido',
                'ci' => '123321',
                'password' => Hash::make('guardia'),
                'rol' => 'guardia',
                'fecha_nacimiento' => Carbon::parse('1990-01-01'),
                'created_at' => '2023-01-01 01:01:01',
            ],
            [
                'name' => 'administrador',
                'email' => 'admin@admin.com',
                'apellido_paterno' => 'apellido',
                'apellid_materno' => 'apellido',
                'ci' => '123123',
                'password' => Hash::make('admin'),
                'rol' => 'administrador',
                'fecha_nacimiento' => Carbon::parse('1990-01-01'),
                'created_at' => '2023-01-01 01:01:01',
            ],
            [
                'name' => 'cliente',
                'email' => 'cliente@cliente.com',
                'apellido_paterno' => 'apellido',
                'apellido_materno' => 'apellido',
                'ci' => '1234567890',
                'password' => Hash::make('cliente'),
                'rol' => 'cliente',
                'fecha_nacimiento' => Carbon::parse('1990-01-01'),
                'created_at' => '2023-01-01 01:01:01',
            ],


        ]);

    }
}