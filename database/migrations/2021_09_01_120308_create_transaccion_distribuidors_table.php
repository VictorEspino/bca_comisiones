<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransaccionDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaccion_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->integer('pedido');
            $table->integer('numero_distribuidor');
            $table->string('distribuidor');
            $table->date('fecha');
            $table->string('tipo_venta');
            $table->string('cliente');
            $table->string('contrato');
            $table->float('importe');
            $table->string('servicio');
            $table->string('producto');
            $table->string('mdn');
            $table->string('imei');
            $table->integer('plazo');
            $table->float('desc_multilinea');
            $table->boolean('eq_sin_costo');
            $table->boolean('credito');
            $table->string('razon_cr0');
            $table->float('comision');
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
        Schema::dropIfExists('transaccion_distribuidors');
    }
}
