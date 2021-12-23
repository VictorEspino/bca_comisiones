<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeBackDetalleDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_back_detalle_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->string('plan');
            $table->string('cuenta');
            $table->string('contrato');
            $table->string('dn');
            $table->date('fecha_activacion');
            $table->date('fecha_baja');
            $table->float('renta');
            $table->string('tipo_baja');
            $table->string('propiedad');
            $table->float('comision');
            $table->float('equipo');
            $table->float('cb');
            $table->integer('numero_distribuidor');
            $table->foreignId('calculo_id');
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
        Schema::dropIfExists('charge_back_detalle_distribuidors');
    }
}
