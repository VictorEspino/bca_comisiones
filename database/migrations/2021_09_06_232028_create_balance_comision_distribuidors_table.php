<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceComisionDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_comision_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_distribuidor");
            $table->string("distribuidor");
            $table->integer("uds_activacion");
            $table->float("renta_activacion");
            $table->float("comision_activacion");
            $table->integer("uds_aep");
            $table->float("renta_aep");
            $table->float("comision_aep");
            $table->integer("uds_renovacion");
            $table->float("renta_renovacion");
            $table->float("comision_renovacion");
            $table->integer("uds_rep");
            $table->float("renta_rep");
            $table->float("comision_rep");
            $table->integer("uds_seguro");
            $table->float("renta_seguro");
            $table->float("comision_seguro");
            $table->integer("uds_addon");
            $table->float("renta_addon");
            $table->float("comision_addon");
            $table->float("comision_final");
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
        Schema::dropIfExists('balance_comision_distribuidors');
    }
}
