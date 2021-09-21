<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlertasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->string("contrato");
            $table->string("tipo_venta");
            $table->string("plan");
            $table->date("fecha");
            $table->string("importe");
            $table->string("numero_empleado");
            $table->string("empleado");
            $table->integer("udn");
            $table->string("pdv");
            $table->integer("tipo");
            $table->string("periodo");
            $table->foreignId("conciliacion_id");
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
        Schema::dropIfExists('alertas');
    }
}
