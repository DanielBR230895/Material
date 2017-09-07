<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonasMatriculaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personas__matricula', function (Blueprint $table) {
            $table->char('identificacion',15);
            $table->string("nombre", 40);
            $table->string("apellido1", 40);
            $table->string("apellido2", 40)->nullable();
            $table->char("genero", 1);
            $table->string("email", 70)->nullable();
            $table->string("residencia", 150);
            $table->string("telefono", 15)->nullable();
            $table->char("tipo_persona", 2);
            $table->char('carrera', 20)->nullable();
            $table->date("fecha_nacimiento");

            $table->primary("identificacion");
            $table->foreign("carrera")->references("codigo")->on("carreras")->onDelete("set null");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personas__matricula');
    }
}
