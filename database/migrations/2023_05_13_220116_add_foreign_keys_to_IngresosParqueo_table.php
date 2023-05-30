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
        Schema::table('IngresosParqueo', function (Blueprint $table) {
            $table->foreign(['id_bloque'], 'fk_ingreso_bloque')->references(['id_bloque'])->on('Bloques')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_espacio'], 'fk_ingreso_espacio')->references(['id_espacio'])->on('Espacios')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_guardia'], 'fk_ingreso_guardia')->references(['id_guardia'])->on('Guardias')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_vehiculo'], 'fk_ingreso_vehiculo')->references(['id_vehiculo'])->on('Vehiculos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_reserva'], 'fk_reserva_ingres')->references(['id_reserva'])->on('Reservas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('IngresosParqueo', function (Blueprint $table) {
            $table->dropForeign('fk_ingreso_bloque');
            $table->dropForeign('fk_ingreso_espacio');
            $table->dropForeign('fk_ingreso_guardia');
            $table->dropForeign('fk_ingreso_vehiculo');
            $table->dropForeign('fk_reserva_ingres');
        });
    }
};
