<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->integer("director");
            $table->integer('regional');
            $table->string("region");
            $table->foreignId("id_gerente");
            $table->integer("udn");
            $table->string("pdv");
            $table->string("esquema");
            $table->integer("activaciones");
            $table->integer("aep");
            $table->integer("renovaciones");
            $table->integer("rep");
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
        Schema::dropIfExists('cuotas');
    }
}
