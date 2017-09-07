<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Matricula;

use App\Messages\Mensaje;

use App\Persona_Matricula;

use App\Http\Controllers\PersonasMatriculaController;

use App\OfertaCocurricular;

use App\Taller;

use App\Ciclo;

use App\Usuario;

use Mail;

use PDF;

use DB;

use Datetime;

use App\Mail\ComprobanteMatricula;

use Illuminate\Support\Facades\Auth;


class MatriculasController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view("matricula");
  }

  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    //
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {
    try{
      //verificar cupo maximo
      $grupo = OfertaCocurricular::findOrFail($request->grupo);

      date_default_timezone_set('America/Costa_Rica');
      $hoy = new DateTime();

      $ciclo = Ciclo::where('periodo',$grupo->periodo_c)
      ->where('anno', $grupo->anno_c)
      ->where('tipo_periodo', $grupo->tipo_periodo_c)->first();

      $inicio_m = date_create_from_format("Y-m-d",$ciclo->inicio_matricula);
      $final_m = date_create_from_format("Y-m-d",$ciclo->final_matricula);

      //Fechas Permitida para matricular
      if($inicio_m > $hoy  || $final_m < $hoy)
        return response()->json(['status'=>'error', 'msg'=>'Se encuentra fuera del tiempo de matrícula ']);


      //verificar  que aun exita cupo
      if($grupo->cupoActual == $grupo->cupoMaximo)
        return response()->json(['status'=>'error', 'msg'=>Mensaje::$matriculaErrorCupo]);


      //verificar si cumple con requisitos
      if(!$this->cumpleRequisito( $request->persona_matricula ,$grupo->taller)
      && !$request->has('LevantarRequiso'))
        return response()->json(['status'=>'error','msg'=>Mensaje::$matriculaErroRequisito]);


      //Persona no existe, entonces se agrega la persona a la base de datos.
      //Si ya existe, se actualiza su campo tipo_persona.
      if (null === Persona_Matricula::find($request->persona_matricula) )
        PersonasMatriculaController::storeFromMatricula($request);

      else
        PersonasMatriculaController::UpdateTipoPersonaFromMatricula($request);


      //guarda los datos de la matricual
      $matricula = new Matricula;
      $matricula->persona_matricula = $request->persona_matricula;
      $matricula->grupo = $request->grupo;
      $matricula->numReciboFUNDAUNA = $request->fundaUNA;
      $matricula->calificacion = 'SA';
      $matricula->matriculador = Auth::user()->id;
      $matricula->tipo_persona = $request->tipo_persona;
      $matricula->carrera = $request->carrera;

      $matricula->save();


      //aumenta cupo de grupo
      $grupo->cupoActual = $grupo->cupoActual + 1;
      $grupo->save();

      //Envia comprobante a correo de talleres
      $this->comprobanteEmailDefault($request->persona_matricula,$request->grupo);

      return response()->json(['status'=>'ok', 'msg'=>Mensaje::$matriculaOK]);

    }
    catch(\Illuminate\Database\Eloquent\ModelNotFoundException $ex){
      //error grupo no encontrado
      return response()->json(['status'=>'error', 'msg'=> Mensaje::$matriculaErrorGrupo]);
    }
    catch(\PDOException $ex){
      //error matricula duplicada
      return response()->json(['status'=>'error', 'msg'=> Mensaje::$matriculaErrorDuplicada]);
    }
    catch(\Exception $ex){
      //error inesperado en el servidor
      return response()->json(['status'=>'error', 'msg'=> 'Ocurrió un error al Matricular, Codigo: '. $ex->getMessage()]);
    }
  }



  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function cumpleRequisito($persona_matriculaID,$tallerID){
    $taller = Taller::find($tallerID);

    //verifica si el taller tiene requisito
    if($taller->requisito == null)
    return true;

    //hace la consulta
    $results = DB::select('
    select taller
    from oferta_cocurriculares,
    (select grupo
    from matriculas
    where persona_matricula = :id) as historial_m
    where historial_m.grupo = oferta_cocurriculares.grupo
    and oferta_cocurriculares.taller = :taller',
    ['id' => $persona_matriculaID, 'taller'=> $taller->requisito ]);

    //si la consulta retorna algun resultado quiere decir que la
    //persona ya curso el taller de requisito.
    return isset($results[0]);
  }




  /**
  * Enviar comporbante por email a la persona matriculada
  *
  * @param  String $persona_matricula
  * @param  String $grupo
  * @return \Illuminate\Http\Response
  */
  public function comprobanteEmail($persona_matricula, $grupoID,$anno, $tipo_periodo, $periodo, $inicioL,$fechaF)
  {
    $matriculado = Persona_Matricula::find($persona_matricula);
    $grupo = OfertaCocurricular::find($grupoID);
    $taller = Taller::find($grupo->taller);
    $instructor = Usuario::find($grupo->idInstructor);

    $correoInstructor = isset($instructor)?$instructor->Email :'';

    if($tipo_periodo == 'O')
      $tipo_periodo = 'Ordinario';
    else {
      $tipo_periodo ='Extraordinario';
    }

    Mail::to($matriculado->email)
        ->send(new ComprobanteMatricula($persona_matricula,
                $matriculado->nombre,
                $matriculado->apellido1,
                $matriculado->apellido2,
                $taller->nombre,
                $grupo->numeroGrupo,
                isset($instructor)? $instructor->Nombre.' '.$instructor->Apellido1.' '.$instructor->Apellido2: "Sin asignar",
                $grupo->lugar,
                $grupo->horario,
                Auth::user()->Nombre.' '.Auth::user()->Apellido1.', ID:'.Auth::user()->id,
                $anno, $tipo_periodo, $periodo,$correoInstructor,$inicioL,$fechaF)
  );

  return response()->json(['status'=>'ok', 'msg'=> 'Email enviado exitosamente']);
}


 /**
  * Enviar comporbante por  defecto a Email  de TAlleres Culturales.
  *
  * @param  String $persona_matricula
  * @param  String $grupo
  * @return \Illuminate\Http\Response
  */

  public function comprobanteEmailDefault($persona_matricula, $grupoID)
  {
    $matriculado = Persona_Matricula::find($persona_matricula);
    $grupo       = OfertaCocurricular::find($grupoID);
    $taller      = Taller::find($grupo->taller);
    $instructor  = Usuario::find($grupo->idInstructor);

    $ciclo = Ciclo::where('periodo',$grupo->periodo_c)
     ->where('anno', $grupo->anno_c)
     ->where('tipo_periodo', $grupo->tipo_periodo_c)->first();


    $correoInstructor = isset($instructor)?$instructor->Email :'';

    $ciclo->tipo_periodo  = $ciclo->tipo_periodo == 'O'? 'Ordinario' :'Extraordinario' ;


    Mail::to(config('mail.mail_cc', 'comprobantestalleres@gmail.com')) //se envia el email al correo configurado en MAIL_CC en env, sino por defecto se envia a comprobantestalleres@gmail.com
        ->send(new ComprobanteMatricula($persona_matricula,
                $matriculado->nombre,
                $matriculado->apellido1,
                $matriculado->apellido2,
                $taller->nombre,
                $grupo->numeroGrupo,
                isset($instructor)? $instructor->Nombre.' '.$instructor->Apellido1.' '.$instructor->Apellido2: "Sin asignar",
                $grupo->lugar,
                $grupo->horario,
                Auth::user()->Nombre.' '.Auth::user()->Apellido1.', ID:'.Auth::user()->id,
                $ciclo->anno, $ciclo->tipo_periodo, $ciclo->periodo, $correoInstructor,
                $ciclo->inicio,$ciclo->final)
    );

    return true;
}




/**
* Enviar comporbante por pdf
*
* @param  String $persona_matricula
* @param  String $grupo
* @return \Illuminate\Http\Response
*/
public function comprobantePdf($persona_matricula, $grupoID, $anno, $tipo_periodo, $periodo, $inicioL, $fechaF)
{
  $matriculado= Persona_Matricula::find($persona_matricula);
  $grupo      = OfertaCocurricular::find($grupoID);
  $taller     = Taller::find($grupo->taller);
  $instructor = Usuario::find($grupo->idInstructor);
  $correoInstructor = isset($instructor)?$instructor->Email :'';

  if($tipo_periodo == 'O')
    $tipo_periodo = 'Ordinario';
  else {
    $tipo_periodo ='Extraordinario';
  }

  $data = [
    'id'    =>$persona_matricula,
    'nombre'=> $matriculado->nombre,
    'apellido1'=> $matriculado->apellido1,
    'apellido2'=> $matriculado->apellido2,
    'taller'=> $taller->nombre,
    'grupo'=> $grupo->numeroGrupo,
    'instructor'=> isset($instructor)? $instructor->Nombre.' '.$instructor->Apellido1.' '.$instructor->Apellido2: "Sin asignar",
    'lugar'=> $grupo->lugar,
    'horario'=>$grupo->horario,
    'matriculador'=> Auth::user()->Nombre.' '.Auth::user()->Apellido1.', ID:'.Auth::user()->id,
    'anno'  => $anno,
    'tipo_periodo'=> $tipo_periodo,
    'periodo' => $periodo,
    'correoInstructor' => $correoInstructor,
    'inicioL'=> $inicioL,
    'fechaF' => $fechaF
  ];
  $pdf = PDF::loadView('comprobanteMatriculaView', $data);
  return $pdf->download('Comprobande Matricula.pdf');
}


/**
* Display the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function show($id)
{
  //
}

/**
* Show the form for editing the specified resource.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function edit($id)
{
  //
}

/**
* Update the specified resource in storage.
*
* @param  \Illuminate\Http\Request  $request
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function update(Request $request, $id)
{
  //
}

/**
* Remove the specified resource from storage.
* Desmatricular a un estudiante de un taller.
*
* @param  string $persona__matricula, int $grupo
* @return \Illuminate\Http\Response
*/
public function destroy($personas__matricula, $grupo)
{
  $matricula = Matricula::where('grupo',$grupo)->where("persona_matricula",
  $personas__matricula)->first();


  if(!isset($matricula))
  return response()->json(['status'=>'error', 'msg'=>Mensaje::$matriculaErrorNoMatriculado]);

  //liberar un cupo en cupo actual del grupo del taller
  $grupoTaller = OfertaCocurricular::find($grupo);
  $grupoTaller->cupoActual = $grupoTaller->cupoActual - 1;
  $grupoTaller->save();

  $matricula->delete();
  return response()->json(['status'=>'ok', 'msg'=>Mensaje::$retiroOK]);

}



public function asignaCondicionParticipativa($id_persona,$grupo,$condicion)
{
  $matricula = Matricula::where('grupo',$grupo)->where("persona_matricula",
  $id_persona)->first();

  if(!isset($matricula))
    return response()->json(['status'=>'error', 'msg'=> 'Persona no encontrada']);

  $matricula->calificacion = $condicion;
  $matricula->asignador = Auth::user()->id;
  $matricula->save();
  return response()->json(['status'=>'ok', 'msg'=>Mensaje::$asignadoOK]);
}

}
