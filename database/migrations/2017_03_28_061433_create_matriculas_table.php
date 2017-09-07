<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMatriculasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->char('persona_matricula',15);
            $table->unsignedInteger('grupo');
            $table->char('calificacion', 4)->default('SA')->nullable();
            $table->string('numReciboFUNDAUNA', 50)->nullable();
            $table->string('matriculador');
            $table->string('asignador')->nullable();
            //$table->char( 'tipo_persona',2)->nullable();
            //$table->char( 'carrera',20)->nullable();



            $table->foreign('asignador')->references('id')->on('usuarios')
                                                            ->onDelte('NO ACTION');

            //$table->foreign('carrera')->references('codigo')->on('carreras')
            //                                                ->onDelte('NO ACTION');

            $table->foreign('matriculador')->references('id')->on('usuarios')
                                                            ->onDelte('NO ACTION');


            $table->foreign('persona_matricula')->references('identificacion')
                                                ->on('personas__matricula')
                                                ->onDelete('cascade');

            $table->foreign('grupo')->references('grupo')
                                                ->on('oferta_cocurriculares')
                                                ->onDelete('cascade');

            $table->primary(['persona_matricula', 'grupo']);


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matriculas');
    }
}
