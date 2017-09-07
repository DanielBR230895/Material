<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Taller extends Model
{
   public $table = "talleres";
   public $primaryKey = 'codigo';
   public $incrementing = false;
   public $timestamps = false;

}
