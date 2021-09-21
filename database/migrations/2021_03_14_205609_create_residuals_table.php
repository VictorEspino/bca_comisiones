<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResidualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('residuals', function (Blueprint $table) {
            $table->id();
            $table->string("contrato");
            $table->string("plan");
            $table->float("comision");
            $table->string("estatus");
            $table->string("marca");
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
        Schema::dropIfExists('residuals');
    }
}
