<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResidualDistribuidorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('residual_distribuidors', function (Blueprint $table) {
            $table->id();
            $table->integer('numero_distribuidor');
            $table->string('concepto');
            $table->float('residual');
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
        Schema::dropIfExists('residual_distribuidors');
    }
}
