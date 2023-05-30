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
        Schema::create('Espacios', function (Blueprint $table) {
            $table->string('id_espacio', 15)->primary();
            $table->string('estado', 45)->nullable();
            $table->string('bloque', 15)->nullable()->index('fk_espacio_bloque_idx');
            $table->unsignedInteger('id_ingreso')->nullable()->index('fk_espacio_ingreso_idx');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Espacios');
    }
};
