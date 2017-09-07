<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Persona_Matricula;

use App\Carrera;

use App\Messages\Mensaje;

class DesmatriculaController extends Controller
{

  const tipos = array( "AM" =>'Adulto Mayor', 'A'=>'Autorización', 'C'=>'Comunidad', 'EU'=>'Estudiante UNA', "F"=>"Funcionario");
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('desmatricula');
  }

  public function destroy($id)
  {
    $persona = Persona_Matricula::find($id);
    if(!isset($persona))
    return response()->json(['status' => 'error', 'msg' => Mensaje::$matriculadoErrorNoEnc]);

    $persona->delete();
    return response()->json(['status' => 'ok', 'msg' => Mensaje::$matriculadoEliminadoOk]);
  }
}
