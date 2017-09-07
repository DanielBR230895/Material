<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ciclo extends Model
{
	use Traits\HasCompositePrimaryKey;

		public $primaryKey = array('periodo', 'tipo_periodo', 'anno');
		public $incrementing = false;
    	public $timestamps = false;
}
