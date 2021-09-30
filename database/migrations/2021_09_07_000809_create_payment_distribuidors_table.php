<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_distribuidor");
            $table->string("distribuidor");
            $table->float("comision");
            $table->float("residual");
            $table->float("adelantos");
            $table->float("charge_back");
            $table->float("a_pagar");
            $table->string("pdf");
            $table->string("xml");
            $table->string("clabe");
            $table->string("titular");
            $table->foreignId("calculo_id");
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
        Schema::dropIfExists('payment_distribuidors');
    }
}
