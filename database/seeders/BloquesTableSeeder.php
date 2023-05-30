<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BloquesTableSeeder extends Seeder
{

    public function run(): void
    {
        DB::table('Bloques')->insert([
            'id_bloque' => 'ZZZ',
            'detalle' => 'no_implementado',
        ]);
    }
}
