<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReservasTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Reservas')->insert([
            [
                'id_espacio' => '1A',
                'id_vehiculo' => '2',
                'reservada_desde_fecha' => '2023-05-16',
                'reservada_desde_hora' => '06:45',
                'reservada_hasta_fecha' => '2023-05-16',
                'reservada_hasta_hora' => '07:00',
                'fecha_creada' => '2023-05-15',
                'hora_creada' => '21:30',
                'id_usuario' => '3',
            ],
            [
                'id_espacio' => '1A',
                'id_vehiculo' => '2',
                'reservada_desde_fecha' => '2023-05-16',
                'reservada_desde_hora' => '08:00',
                'reservada_hasta_fecha' => '2023-05-17',
                'reservada_hasta_hora' => '08:00',
                'fecha_creada' => '2023-05-15',
                'hora_creada' => '21:33',
                'id_usuario' => '3',
            ],
            [
                'id_espacio' => '1A',
                'id_vehiculo' => '2',
                'reservada_desde_fecha' => '2023-05-17',
                'reservada_desde_hora' => '07:45',
                'reservada_hasta_fecha' => '2023-05-17',
                'reservada_hasta_hora' => '23:00',
                'fecha_creada' => '2023-05-15',
                'hora_creada' => '21:45',
                'id_usuario' => '3',
            ],
        ]);

        DB::table('Reservas')->insert([
            [
                'id_espacio' => '2A',
                'reservada_desde_fecha' => '2023-05-16',
                'reservada_desde_hora' => '06:45',
                'reservada_hasta_fecha' => '2023-05-16',
                'reservada_hasta_hora' => '07:00',
                'fecha_creada' => '2023-05-15',
                'hora_creada' => '21:30',
                'id_usuario' => '1',
                'placa_vehiculo' => '2020ABC',
            ],
        ]);
    }
}
