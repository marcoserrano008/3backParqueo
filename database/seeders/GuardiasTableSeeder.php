<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GuardiasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Guardias')->insert([
            'id_guardia' => '1234567890',
            'fecha_incorporacion' => '2023-05-13',
            'id_usuario' => '1',
        ]);
    }
}
