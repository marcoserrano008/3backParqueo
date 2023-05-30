<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspaciosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            for ($j = 'A'; $j <= 'L'; $j++) {
                $id_espacio = $i . $j;
                DB::table('Espacios')->insert([
                    'id_espacio' => $id_espacio,
                    'estado' => 'libre',
                    'bloque' => 'ZZZ',
                ]);
            }
        }
    }
}
