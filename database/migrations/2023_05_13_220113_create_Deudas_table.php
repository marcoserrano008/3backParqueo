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
            $table->integer('id_deuda')->primary();
            $table->unsignedInteger('id_salida')->nullable()->index('fk_id_salida_idx');
            $table->integer('monto')->nullable();
            $table->string('id_precio', 25)->nullable()->index('fk_deuda_precio_idx');
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
