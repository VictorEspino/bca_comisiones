<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_empleado");
            $table->float("comision_ventas");
            $table->float("comision_gerente");
            $table->float("comision_regional");
            $table->float("comision_director");
            $table->float("adeudo_anterior");
            $table->float("charge_back");
            $table->float("sueldo");
            $table->integer("modalidad");
            $table->string("estatus");
            $table->float("a_pagar");
            $table->float("adeudo");
            $table->float("subsidio");
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
        Schema::dropIfExists('payments');
    }
}
