<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCiclosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ciclos', function (Blueprint $table) {
            $table->integer('periodo');
			      $table->char('tipo_periodo', 1);
			      $table->integer('anno');
            $table->date('inicio');
            $table->date('final');
            $table->date('inicio_matricula');
            $table->date('final_matricula');
            $table->date('limite_condicion');
			      $table->primary(['periodo', 'tipo_periodo','anno']);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ciclos');
    }
}
