<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpleadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->integer("numero_empleado");
            $table->string("nombre");
            $table->integer("udn");
            $table->string("pdv");
            $table->string("puesto");
            $table->date("ingreso");
            $table->float("adeudo");
            $table->foreignID("calculo_id");
            $table->string("estatus");
            $table->float("sueldo");
            $table->integer("modalidad");
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
        Schema::dropIfExists('empleados');
    }
}
