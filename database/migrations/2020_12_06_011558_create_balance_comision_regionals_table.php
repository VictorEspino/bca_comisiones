<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBalanceComisionRegionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('balance_comision_regionals', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_empleado");
            $table->string("udn");
            $table->integer("uds_activacion");
            $table->integer("uds_aep");
            $table->integer("uds_renovacion");
            $table->integer("uds_rep");
            $table->float("porc_cierre_activacion");
            $table->float("porc_cierre_aep");
            $table->float("porc_cierre_renovacion");
            $table->float("porc_cierre_rep");
            $table->integer("cuota_activacion");
            $table->float("alcance_activacion");
            $table->integer("cuota_aep");
            $table->float("alcance_aep");
            $table->integer("cuota_renovacion");
            $table->float("alcance_renovacion");
            $table->integer("cuota_rep");
            $table->float("alcance_rep");
            $table->float("comision_directa_activacion");
            $table->float("comision_directa_aep");
            $table->float("comision_directa_renovacion");
            $table->float("comision_directa_rep");
            $table->float("comision_directa_seguro");
            $table->float("comision_directa_addon");
            $table->float("comision_final_activacion");
            $table->float("comision_final_aep");
            $table->float("comision_final_renovacion");
            $table->float("comision_final_rep");
            $table->float("comision_final_seguro");
            $table->float("comision_final_addon");
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
        Schema::dropIfExists('balance_comision_regionals');
    }
}
