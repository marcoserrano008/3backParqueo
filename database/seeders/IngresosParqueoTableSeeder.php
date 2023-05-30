<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngresosParqueoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('IngresosParqueo')->insert([
            "id_vehiculo" => '2',
            "hora_ingreso" => '06:30',
            "fecha_ingreso" => '2023-05-16',
            "id_espacio" => '8E',
            "id_reserva" => '4',
        ]);
        DB::table('IngresosParqueo')->insert([
            "id_vehiculo" => '2',
            "hora_ingreso" => '06:30',
            "fecha_ingreso" => '2023-05-17',
            "id_espacio" => '10E',
        ]);
        DB::table('IngresosParqueo')->insert([
            "hora_ingreso" => '06:30',
            "fecha_ingreso" => '2023-05-17',
            "id_espacio" => '9E',
            "placa_vehiculo" => '1232CDB',
        ]);
    }
}
