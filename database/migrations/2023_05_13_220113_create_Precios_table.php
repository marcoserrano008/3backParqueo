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
        Schema::create('Precios', function (Blueprint $table) {
            $table->string('id_precio', 25)->primary();
            
            $table->integer('primera_hora')->nullable();
            $table->integer('hora_adicional')->nullable();
            $table->date('fecha')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Precios');
    }
};
