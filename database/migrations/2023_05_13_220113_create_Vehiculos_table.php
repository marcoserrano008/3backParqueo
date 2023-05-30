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
        Schema::create('Vehiculos', function (Blueprint $table) {
            $table->increments('id_vehiculo')->start(1000);
            $table->integer('id_cliente')->nullable()->index('fk_id_cliente_idx');
            $table->string('marca', 45)->nullable();
            $table->string('color', 45)->nullable();
            $table->string('modelo', 45)->nullable();
            $table->string('placa', 45)->nullable();
            
            $table->string('observacion', 20)->nullable();
            
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Vehiculos');
    }
};
