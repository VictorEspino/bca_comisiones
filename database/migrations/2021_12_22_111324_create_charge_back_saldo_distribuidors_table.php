<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeBackSaldoDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_back_saldo_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calculo_id');
            $table->integer('numero_distribuidor');
            $table->float('saldo_anterior');
            $table->float('aplicado');
            $table->float('nuevo_saldo');
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
        Schema::dropIfExists('charge_back_saldo_distribuidors');
    }
}
