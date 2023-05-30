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
        Schema::table('Cobros', function (Blueprint $table) {
            $table->foreign(['id_deuda'], 'fk_cobro_deuda')->references(['id_deuda'])->on('Deudas')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Cobros', function (Blueprint $table) {
            $table->dropForeign('fk_cobro_deuda');
        });
    }
};
