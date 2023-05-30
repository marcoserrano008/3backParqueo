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
        Schema::create('SalidasParqueo', function (Blueprint $table) {
            $table->increments('id_salida')->start(1000);
            $table->unsignedInteger('id_vehiculo')->nullable()->index('fk_id_vehiculo_idx');
            $table->time('hora_salida')->nullable();
            $table->date('fecha_salida')->nullable();
            $table->unsignedInteger('id_ingreso')->nullable()->index('fk_id_salida_idx');
            $table->string('placa_vehiculo', 20)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('SalidasParqueo');
    }
};
