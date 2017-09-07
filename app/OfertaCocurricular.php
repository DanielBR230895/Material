<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OfertaCocurricular extends Model
{
    public $table = "oferta_cocurriculares";
    public $primaryKey = 'grupo';
    public $timestamps = false;
    public $incrementing = true;
}
