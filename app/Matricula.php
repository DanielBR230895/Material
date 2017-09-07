<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
  	  use Traits\HasCompositePrimaryKey;

      public $primaryKey = array('persona_matricula', 'grupo');
      public $incrementing = false;
      public $timestamps = false;
}
