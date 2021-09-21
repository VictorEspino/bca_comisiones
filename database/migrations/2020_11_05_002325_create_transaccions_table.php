<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaccions', function (Blueprint $table) {
            $table->id();

            $table->integer('pedido');
            $table->integer('numero_empleado');
            $table->string('empleado');
            $table->date('fecha');
            $table->string('region');
            $table->integer('udn');
            $table->string('pdv');
            $table->string('tipo_venta');
            $table->string('transaccion');
            $table->string('contrato');
            $table->float('importe');
            $table->string('servicio');
            $table->string('producto');
            $table->string('seguro');
            $table->string('add_ons');
            $table->string('plazo');
            $table->string('estado');
            $table->string('canal_ventas');
            $table->string('subcanal');
            $table->string('tipo_de_venta_2');
            $table->string('desc_multilinea');
            $table->boolean('eq_sin_costo');
            $table->boolean('credito');
            $table->string('razon_cr0');
            $table->float('comision_venta');
            $table->float('comision_supervisor_l1');
            $table->float('comision_supervisor_l2');
            $table->float('comision_supervisor_l3');
            $table->integer('ejecutivoCC');
            $table->integer('supervisorCC');
            $table->float('comisionCC');
            $table->float('comision_supervisor_cc');
            $table->string("periodo");
            $table->boolean("cb_att");
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
        Schema::dropIfExists('transaccions');
    }
}
