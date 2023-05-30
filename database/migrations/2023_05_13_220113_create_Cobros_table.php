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
        Schema::create('Cobros', function (Blueprint $table) {
            $table->integer('id_cobro')->primary();
            $table->integer('id_deuda')->nullable()->index('fk_id_deuda_idx');
            $table->date('fecha_cobrada')->nullable();
            $table->time('hora_cobrada')->nullable();
            $table->string('tipo_pago', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Cobros');
    }
};
