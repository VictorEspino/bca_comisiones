<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceComisionVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_comision_ventas', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_empleado");
            $table->string("nombre");
            $table->string("puesto");
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
            $table->integer("esquema");
            $table->boolean("cumple_objetivo");
            $table->float("porcentaje_cobro");
            $table->float("comision_final_activacion");
            $table->float("comision_final_aep");
            $table->float("comision_final_renovacion");
            $table->float("comision_final_rep");
            $table->float("comision_final_seguro");
            $table->float("comision_final_addon");
            $table->float("comision_final");
            $table->string("comentario");
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
        Schema::dropIfExists('balance_comision_ventas');
    }
}
