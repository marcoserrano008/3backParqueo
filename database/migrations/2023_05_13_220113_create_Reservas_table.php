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
        Schema::create('Reservas', function (Blueprint $table) {
            $table->increments('id_reserva')->start(1000);
            $table->string('id_espacio', 15)->nullable()->index('fk_id_espacio_idx');
            $table->unsignedInteger('id_vehiculo')->nullable()->index('fk_id_vehiculo_idx');
            $table->date('reservada_desde_fecha')->nullable();
            $table->time('reservada_desde_hora')->nullable();
            $table->date('reservada_hasta_fecha')->nullable();
            $table->time('reservada_hasta_hora')->nullable();
            $table->date('fecha_creada')->nullable();
            $table->time('hora_creada')->nullable();
            $table->string('placa_vehiculo', 20)->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable()->index('fk_reserva_usuario_idx');
            $table->date('reservada_desde_fechaG1')->nullable();
            $table->time('reservada_desde_horaG1')->nullable();
            $table->date('reservada_desde_fechaG2')->nullable();
            $table->time('reservada_desde_horaG2')->nullable();

            $table->string('tipo')->default(null)->nullable();
            $table->double('costo')->default(null)->nullable();
            $table->string('pagada')->default('no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Reservas');
    }
};
