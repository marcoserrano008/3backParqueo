<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalidasParqueoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('SalidasParqueo')->insert([
            "id_vehiculo" => '2',
            "hora_salida" => '18:30',
            "fecha_salida" => '2023-05-16',
            "id_ingreso" => '1',
        ]);
    }
}
