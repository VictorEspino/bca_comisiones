<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeBackSaldoInternosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_back_saldo_internos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculo_id');
            $table->integer('saldo_inicial')->default(0);
            $table->float('saldo_inicial')->default(0);
            $table->float('nuevo_charge_back')->default(0);
            $table->float('aplicado')->default(0);
            $table->float('saldo_final')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('charge_back_saldo_internos');
    }
}
