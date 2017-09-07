<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\OfertaCocurricular;

use App\Messages\Mensaje;

use App\Usuario;

use App\Taller;

use DB;

use Auth;

class OfertaCoCurricularController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  *
  * @return \Illuminate\Http\Response
  */

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('oferta_cocurricular');
  }

  /**
  * Retorna todos los personas matriculadas
  *
  * @param
  * @return Json con ciclos
  */
  public function getAll(){
    $grupos = $this->agregarNombreTaller($this->agregarNombreInstructor(OfertaCocurricular::all()));
    return response()->json($grupos);
    //return response()->json(OfertaCocurricular::all());
  }


  /**
  * Retorna todos los grupos de talleres que ha matriculado una persona
  *
  * @param  string  $id //id de la persona
  * @return string  json con todos los grupos que ha matriculado
  */
  public function getOfertaCoCurricularEstudiante($id = '')
  {

    //Averiguar cual de las dos opciones de query es mas rapido

    //Averiguar cual de las dos opciones de query es mas rapido
    //Opc A) Query hecho con subquery
    $ofertasA = DB::select('select OC.taller, OC.numeroGrupo, OC.grupo,  OC.idInstructor, OC.horario,
    OC.periodo_c, OC.tipo_periodo_c, OC.anno_c
    from oferta_cocurriculares as OC,
    (select grupo
    from matriculas
    where persona_matricula = :id) as historial_m,
    (select tipo_periodo, anno, periodo
    from ciclos
    where final >= CURDATE()) as AC
    where historial_m.grupo = OC.grupo
    AND OC.periodo_c = AC.periodo
    AND OC.tipo_periodo_c = AC.tipo_periodo
    AND OC.anno_c = AC.anno',
    ['id' => $id]);
    $ofertas = collect($ofertasA);
    //Opc B) Query hecho con inner join
    //$ofertas = DB::table('oferta_cocurriculares')
    //        ->join('matriculas', function ($join) {
    //                $join->where('matriculas.persona_matricula', '=', $id);
    //                $join->on('matriculas.grupo', '=', 'oferta_cocurriculares.grupo');
    //              })
    //        ->select('oferta_cocurriculares.taller','oferta_cocurriculares.grupo',  'oferta_cocurriculares.numeroGrupo', 'oferta_cocurriculares.idInstructor',
    //                  'oferta_cocurriculares.periodo_c','oferta_cocurriculares.tipo_periodo_c','oferta_cocurriculares.anno_c')
    //        ->get();
    return response()->json( $this->agregarNombreTaller($this->agregarNombreInstructor($ofertas)));
  }



  /**
  * Retorna todos los grupos de talleres que ha matriculado una persona
  *
  * @return string  json con todos los grupos tiene asignado un instructor
  * En dado caos de ser el adminstrador retorna todos los grupos disponibles
  */
  public function getOfertaCoCurricularInstructor()
  {
    //Averiguar cual de las dos opciones de query es mas rapido
    //Opc A) Query hecho con subquery
    if(Auth::user()->Rol=='Administrador'){
      $ofertasA = DB::select('select OC.*
      from oferta_cocurriculares as OC,
      (select periodo , tipo_periodo , anno
      from ciclos
      where limite_condicion >= CURDATE()) as CL
      where CL.periodo = OC.periodo_c AND CL.anno = OC.anno_c AND CL.tipo_periodo = OC.tipo_periodo_c');
    }

    else {
      $ofertasA = DB::select('select OC.*
      from oferta_cocurriculares as OC,
      (select periodo , tipo_periodo , anno
      from ciclos
      where limite_condicion >= CURDATE()) as CL
      where CL.periodo = OC.periodo_c AND CL.anno = OC.anno_c AND CL.tipo_periodo = OC.tipo_periodo_c
      AND OC.idInstructor = :id', [ 'id' => Auth::user()->id ] );
    }
    $ofertas = collect($ofertasA);

    //Opc B) Query hecho con inner join
    //$ofertas = DB::table('oferta_cocurriculares')
    //        ->join('ciclos', function ($join) {
    //                $join->where( 'limite_condicion', '>=', date('Y-m-d'));
    //                $join->on('ciclos.periodo', '=', 'oferta_cocurriculares.periodo_c');
    //                $join->on('ciclos.anno', '=', 'oferta_cocurriculares.anno_c');
    //                $join->on('ciclos.tipo_periodo', '=', 'oferta_cocurriculares.tipo_periodo_c');
    //              })
    //        ->select('oferta_cocurriculares.*')
    //        ->get();

    //return Auth::user()->id;
    return response()->json($this->agregarNombreInstructor( $this->agregarNombreTaller($ofertas)));
  }

  /**
  * Retorna todos los grupos de talleres que ha matriculado una persona
  *
  * @param  String  $grupo -> id del grupo del cual se desea retornar todas las
  *                           personas matriculadas
  * @return string  json con datos de personas matriculadas en el grupo
  */
  public function getOfertaCoCurricularMatriculados($grupo = '')
  {
    $personas = DB::select('select P.*, Matriculados.calificacion
    from personas__matricula as P,
    (select persona_matricula, calificacion
    from matriculas
    where  grupo = :grupo) as Matriculados
    where  P.identificacion = Matriculados.persona_matricula', [ 'grupo' => $grupo] );


    return response()->json($personas);
  }




  /**
  * Retorna todos los personas matriculadas
  *
  * @param  String anno -> anno del ciclo
  * @param  String tipo_periodo -> tipo_periodo del ciclo
  * @param  String periodo -> periodo del ciclo
  * @return Json con grupos de un ciclo
  */
  public function getOfertaCoCurricularCiclo($anno, $tipo_periodo, $periodo){

    $ofertas = DB::select('select OC.*, talleres.nombre as tallerNombre
    from oferta_cocurriculares as OC
    inner join talleres on talleres.codigo = OC.taller
    where  OC.periodo_c = :periodo AND OC.anno_c = :anno
            AND OC.tipo_periodo_c = :tipo_periodo
    order by talleres.nombre, OC.numeroGrupo', [ 'periodo' => $periodo,
            'anno'=> $anno , 'tipo_periodo' =>  $tipo_periodo] );

    return response()->json($this->agregarNombreInstructor(collect($ofertas)));
  }
  
  //Toma la coleccion de grupos y le agrega a cada elemento un atributo con el nombre del requisito;
  //Luego retorna esa coleccion.
  public function agregarNombreTaller($grupos){
    $talleres  = Taller::all()->keyBy('codigo');
    $grupos->map(function($item, $k) use ($talleres){
      if(isset($item->taller))
      $item->tallerNombre= $talleres[$item->taller]->nombre;
      return $item;
    });
    return $grupos;
  }


  public function agregarNombreInstructor($grupos){
    $instructores  = Usuario::all()->keyBy('identificacion');

    $grupos->map(function($item, $k) use ($instructores){
      if(isset($instructores[$item->idInstructor]))
      $item->instructorNombre= $instructores[$item->idInstructor]->Nombre." ".$instructores[$item->idInstructor]->Apellido1." ".$instructores[$item->idInstructor]->Apellido2;
      else
      $item->instructorNombre= '*Pendiente de asignar*';
      return $item;
    });
    return $grupos;
  }


  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)  {
    $grupos = new OfertaCocurricular;
    $grupos->periodo_c = $request->periodo_c;
    $grupos->tipo_periodo_c = $request->tipo_periodo_c;
    $grupos->anno_c = $request->anno;
    $grupos->taller = $request->taller;

    $grupos->numeroGrupo = $request->numeroGrupo;

    if($request->instructor!="")
    $grupos->idInstructor = $request->instructor;

    $grupos->cupoMinimo = $request->cupoMinimo;
    $grupos->cupoMaximo = $request->cupoMaximo;

    if($request->costoComunidad!="")
    $grupos->costoComunidad = $request->costoComunidad;


    if($request->costoAdultoMayor=="")
    $grupos->costoAdultoMayor =0;
    else
    $grupos->costoAdultoMayor = $request->costoAdultoMayor;

    if($request->costoFuncionario=="")
    $grupos->costoFuncionario =0;
    else
    $grupos->costoFuncionario = $request->costoFuncionario;


    if($request->costoEstudiante=="")
    $grupos->costoEstudiante =0;
    else
    $grupos->costoEstudiante = $request->costoEstudiante;

    if($request->lugar=="")
    $grupos->lugar ="Sin asignar";
    else
    $grupos->lugar = $request->lugar;

    if($request->horario=="")
    $grupos->horario ="Sin asignar";
    else
    $grupos->horario = $request->horario;

    //valida cupo
    if($grupos->cupoMinimo > $grupos->cupoMaximo) {
      return response()->json(['status' => 'error' ,'msg' => Mensaje::$grupoErrorCupoMinMax]);
    }


    //grupos de ese ciclo, de ese taller y que contenga el mismo numero de grupo (el q pone gerard)
    $gruposRaw = OfertaCocurricular::where('anno_c',$grupos->anno_c)
    ->where('tipo_periodo_c', $grupos->tipo_periodo_c)
    ->where('periodo_c',$grupos->periodo_c)
    ->where('taller', $grupos->taller)
    ->where('numeroGrupo',$grupos->numeroGrupo)->first();

    //existe un numero de grupo igual (digitado por gerard)
    if (isset($gruposRaw))
    {
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$grupoErrorNumGrupo]);
    }

    $grupos->save();
    return response()->json(['status' => 'ok', 'msg' => Mensaje::$agregaGrupoOk , 'content' => $grupos->toJson()]);


    /*if($grupos->cupoMinimo > $grupos->cupoMaximo) $cond = false;

    if($cond == true){
    $grupos->save();
    //Revisar
    $grupos->tallerNombre = Taller::find($request->taller)->nombre;
    $instructorNombre = Usuario::find($request->idInstructor);
    $grupos->instructorNombre = $instructorNombre->nombre." (".$instructorNombre->id.")";
    return response()->json(['status' => 'ok', 'msg' => Mensaje::$agregaGrupoOk , 'content' => $grupos->toJson()]);
  }

  else
  return response()->json(['status' => 'error' ,'msg' => Mensaje::$grupoErrorCupoMinMax]);
  */
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
public function update(Request $request)
{

  $grupos = OfertaCocurricular::find($request->grupo);
  //$grupos = ;
  $grupos->periodo_c = $request->periodo_c;
  $grupos->tipo_periodo_c = $request->tipo_periodo_c;
  $grupos->anno_c = $request->anno;
  $grupos->taller = $request->taller;

  $grupos->numeroGrupo = $request->numeroGrupo;


  if($request->instructor!="")
  $grupos->idInstructor = $request->instructor;
  else {
    $grupos->idInstructor =null;
  }


  $grupos->cupoMinimo = $request->cupoMinimo;
  $grupos->cupoMaximo = $request->cupoMaximo;

  if($request->costoComunidad=="")
  $grupos->costoComunidad =0;
  else
  $grupos->costoComunidad = $request->costoComunidad;


  if($request->costoAdultoMayor=="")
  $grupos->costoAdultoMayor =0;
  else
  $grupos->costoAdultoMayor = $request->costoAdultoMayor;

  if($request->costoFuncionario=="")
  $grupos->costoFuncionario =0;
  else
  $grupos->costoFuncionario = $request->costoFuncionario;


  if($request->costoEstudiante=="")
  $grupos->costoEstudiante =0;
  else
  $grupos->costoEstudiante = $request->costoEstudiante;

  if($request->lugar=="")
  $grupos->lugar ="Sin asignar";
  else
  $grupos->lugar = $request->lugar;

  if($request->horario=="")
  $grupos->horario ="Sin asignar";
  else
  $grupos->horario = $request->horario;

  if($grupos->cupoMinimo > $grupos->cupoMaximo) {
    return response()->json(['status' => 'error' ,'msg' => Mensaje::$grupoErrorCupoMinMax]);
  }

  $grupos->save();
  return response()->json(['status' => 'ok', 'msg' => Mensaje::$grupoUpdateOK , 'content' => $grupos->toJson()]);
}

/**
* Remove the specified resource from storage.
*
* @param  int  $id
* @return \Illuminate\Http\Response
*/
public function destroy($id)
{
  $grupo = OfertaCocurricular::find($id);

  if(!isset($grupo))
  return response()->json(['status' => 'error', 'msg' => Mensaje::$grupoErrorNoEncon]);

  $grupo->delete();
  return response()->json(['status' => 'ok', 'msg' => Mensaje::$grupoEliminadoOk]);
}

}
