<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeBackInternosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charge_back_internos', function (Blueprint $table) {
            $table->id();
            $table->foreignID('calculo_origen');
            $table->string('pagado_en');
            $table->date('fecha');
            $table->string('servicio');
            $table->float('importe');
            $table->integer('pedido');
            $table->string('contrato');
            $table->string('tipo_venta');
            $table->integer('udn');
            $table->string('pdv');
            $table->integer('numero_empleado');
            $table->float('cb');
            $table->string('rol');
            $table->foreignID('calculo_id');
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
        Schema::dropIfExists('charge_back_internos');
    }
}
