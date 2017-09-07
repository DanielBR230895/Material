<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfertaCocurricularesTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('oferta_cocurriculares', function (Blueprint $table) {
      $table->increments('grupo');

      //ciclo
      $table->integer('periodo_c')->nullable();
      $table->char('tipo_periodo_c', 1)->nullable();
      $table->integer('anno_c')->nullable();



      //taller
      $table->char('taller')->nullable();
      $table->integer('numeroGrupo'); //El que Gerald digita en el sistema.
      //usuario
      $table->string('idInstructor')->nullable();
      //OfertaCocurricular
      $table->integer('cupoMinimo')->default(0);
      $table->integer('cupoActual')->default(0);
      $table->integer('cupoMaximo')->default(0);
      $table->float('costoComunidad')->default(0);
      $table->float('costoAdultoMayor')->default(0);
      $table->float('costoFuncionario')->default(0);
      $table->float('costoEstudiante')->default(0);
      $table->string('lugar')->default('Sin lugar definido');
      $table->string('horario')->default('Sin horario definido');

      $table->foreign("taller")->references("codigo")->on("talleres")->onDelete("set null");
      $table->foreign(["periodo_c", "tipo_periodo_c", "anno_c"] )
      ->references(["periodo", "tipo_periodo", "anno"])->on("ciclos")->onDelete("cascade");
      $table->foreign("idInstructor")->references("id")->on("usuarios")->onDelete("set null");

    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::dropIfExists('oferta_cocurriculares');
  }
}
