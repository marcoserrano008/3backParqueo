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
        Schema::create('Guardias', function (Blueprint $table) {
            $table->integer('id_guardia')->primary();
            $table->date('fecha_incorporacion')->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable()->index('fk_guardia_usuario_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Guardias');
    }
};
