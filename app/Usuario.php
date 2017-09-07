<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
   public $incrementing = false;
   public $timestamps =   false;
  // protected $primaryKey = 'id'; //IMPORTANTE -> comentar en windows, descomentar en linux
  protected $fillable  = [
       'Nombre', 'Apellido1',
  ];

  protected $hidden = [
       'id','password', 'remember_token', 'updated_at',
  ];

}
