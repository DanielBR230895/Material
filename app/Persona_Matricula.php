<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Persona_Matricula extends Model
{
  public $table = "personas__matricula";
  public $primaryKey = 'identificacion';
  public $incrementing = false;
  public $timestamps = false;
}
