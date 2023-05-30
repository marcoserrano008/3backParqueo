<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('SalidasParqueo', function (Blueprint $table) {
            $table->foreign(['id_ingreso'], 'fk_salida_ingreso')->references(['id_ingreso'])->on('IngresosParqueo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_vehiculo'], 'fk_salida_vehiculo')->references(['id_vehiculo'])->on('Vehiculos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('SalidasParqueo', function (Blueprint $table) {
            $table->dropForeign('fk_salida_ingreso');
            $table->dropForeign('fk_salida_vehiculo');
        });
    }
};
