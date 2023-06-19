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
        Schema::table('Deudas', function (Blueprint $table) {
            
            $table->foreign(['id_salida'], 'fk_deuda_salida')->references(['id_salida'])->on('SalidasParqueo')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['id_cliente'], 'fk_deuda_cliente')->references(['id_cliente'])->on('Clientes')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Deudas', function (Blueprint $table) {
            $table->dropForeign('fk_deuda_salida');
            $table->dropForeign('fk_deuda_cliente');
        });
    }
};
