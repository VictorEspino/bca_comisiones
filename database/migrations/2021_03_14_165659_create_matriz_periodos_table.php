<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMatrizPeriodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matriz_periodos', function (Blueprint $table) {
            $table->id();
            $table->string("evaluado");
            $table->string("offset");
            $table->integer("step");
            $table->boolean("att_comision");
            $table->boolean("att_residual");
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
        Schema::dropIfExists('matriz_periodos');
    }
}
