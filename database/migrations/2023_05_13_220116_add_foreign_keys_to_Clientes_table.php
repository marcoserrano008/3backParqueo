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
        Schema::table('Clientes', function (Blueprint $table) {
            
            $table->foreign(['id_usuario'], 'fk_cliente_usuario')->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Clientes', function (Blueprint $table) {
            $table->dropForeign('fk_cliente_usuario');
        });
    }
};
