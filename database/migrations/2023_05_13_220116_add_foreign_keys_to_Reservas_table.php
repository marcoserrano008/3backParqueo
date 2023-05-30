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
        Schema::table('Reservas', function (Blueprint $table) {
            $table->foreign(['id_espacio'], 'fk_id_espacio')->references(['id_espacio'])->on('Espacios')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_vehiculo'], 'fk_id_vehiculo')->references(['id_vehiculo'])->on('Vehiculos')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_usuario'], 'fk_reserva_usuario')->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Reservas', function (Blueprint $table) {
            $table->dropForeign('fk_id_espacio');
            $table->dropForeign('fk_id_vehiculo');
            $table->dropForeign('fk_reserva_usuario');
        });
    }
};
