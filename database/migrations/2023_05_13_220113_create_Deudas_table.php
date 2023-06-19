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
        Schema::create('Deudas', function (Blueprint $table) {
            $table->increments('id_deuda')->start(1000);
            $table->unsignedInteger('id_salida')->nullable()->index('fk_id_salida_idx');
            $table->integer('monto')->nullable();
            $table->integer('id_cliente')->nullable()->index('fk_id_cliente_idx');
            $table->string('estado')->nullable()->default(null);
            $table->dateTime('fecha_hora_pagado')->nullable()->default(null);
            $table->string('tipo_pago')->nullable()->default(null);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Deudas');
    }
};
