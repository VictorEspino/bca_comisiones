<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReclamosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->id();
            $table->string("telefono");
            $table->string("plan");
            $table->string("renta");
            $table->string("propiedad");
            $table->string("iccid")->nullable();
            $table->date("fecha");
            $table->integer("plazo");
            $table->string("contrato");
            $table->string("cuenta")->nullable();
            $table->string("marca");
            $table->string("periodo");
            $table->string("observacion");
            $table->float("comision");
            $table->string("mes");
            $table->foreignID("conciliacion_id");
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
        Schema::dropIfExists('reclamos');
    }
}
