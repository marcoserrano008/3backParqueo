<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehiculoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('Vehiculos')->insert([
        [
            'id_cliente' => '1234567890',
            'marca' => 'marca1',
            'color' => 'rojo',
            'modelo' => 'modelo1',
            'placa' => '3030ABC',
        ],
        [
            'id_cliente' => '1234567890',
            'marca' => 'marca2',
            'color' => 'negro',
            'modelo' => 'modelo2',
            'placa' => '3030DEF',
        ],

        ]);

        DB::table('Vehiculos')->insert([
            [
                'id_cliente' => '1234567890',
                'marca' => 'noMostrar',
                'color' => 'noMostrar',
                'modelo' => 'noMostrar',
                'placa' => 'xxx',
                'observacion' => 'eliminado',
            ],
    
            ]);

        
    }
}
