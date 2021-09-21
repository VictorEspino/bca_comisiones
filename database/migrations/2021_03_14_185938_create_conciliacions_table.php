<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConciliacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conciliacions', function (Blueprint $table) {
            $table->id();
            $table->string("periodo");
            $table->boolean("comisiones_att");
            $table->boolean("residual_att");
            $table->boolean("charge_back_att");
            $table->boolean("terminado");
            $table->foreignId('user_id');
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
        Schema::dropIfExists('conciliacions');
    }
}
