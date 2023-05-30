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
        Schema::table('Vehiculos', function (Blueprint $table) {
            $table->foreign(['id_cliente'], 'fk_id_cliente')->references(['id_cliente'])->on('Clientes')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Vehiculos', function (Blueprint $table) {
            $table->dropForeign('fk_id_cliente');
        });
    }
};
