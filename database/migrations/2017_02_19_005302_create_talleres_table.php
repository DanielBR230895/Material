<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTalleresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('talleres', function (Blueprint $table) {
          $table->char('codigo');
          $table->string('nombre')->unique();
          $table->text('descripcion');
          $table->char('modulo', 10)->nullable();
          $table->char('requisito')->nullable();

          $table->primary('codigo');
          $table->foreign('modulo')->references('codigo')->on('modulos')->onDelete('set null');
          $table->foreign('requisito')->references('codigo')->on('talleres')->onDelete('set null');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('talleres');
    }
}
