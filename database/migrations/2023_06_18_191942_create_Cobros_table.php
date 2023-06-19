<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Cobros', function (Blueprint $table) {
            $table->increments('id_cobro')->start(1000);
            $table->string('placa_vehiculo')->nullable();
            $table->time('hora')->nullable();
            $table->date('fecha')->nullable();
            $table->string('origen')->nullable();
            $table->string('metodo')->nullable();
            $table->string('id_origen')->nullable();
            $table->string('id_usuario')->nullable();
            $table->string('monto')->nullable();
            $table->string('id_espacio')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Cobros');
    }
};
