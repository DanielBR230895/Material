<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuariosTable extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('usuarios', function (Blueprint $table) {
      $table->string('Nombre');
      $table->string('Apellido1');
      $table->string('Apellido2');
      $table->string('password');
      $table->string('telefono');
      $table->string('id')->primary();
      $table->string('identificacion');
      $table->string('Email')->nullable();
      $table->char('Rol');
      $table->boolean('Habilitado');
      $table->timestamps();
      $table->rememberToken();
    });
  }

  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::drop('usuarios');
  }
}
