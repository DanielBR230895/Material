<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Ciclo;

use DB;

use App\Messages\Mensaje;

class CiclosController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('mantenimiento_ciclos');
  }

  public function getAll()
  {
    return response()->json(Ciclo::orderBy('inicio_matricula','desc')->get());
  }

  public function getCiclosDisponiblesMatricula(){
    return json_encode(DB::select('SELECT * from ciclos where DATE(ciclos.inicio_matricula) <= CURDATE() AND DATE(ciclos.final_matricula) >= CURDATE() 
                                   Order by inicio_matricula desc '));
  }

  public function getCiclosDisponiblesOferta(){
    return json_encode(DB::select('SELECT * from ciclos where DATE(ciclos.inicio) <= CURDATE() and DATE(ciclos.final) >= CURDATE()
                                    Order by inicio_matricula desc '));
  }


  public function getCiclo($periodo, $anno, $tipo)
  {

    $ciclo = Ciclo::where('periodo',$periodo)
                    ->where('anno', $anno)
                    ->where('tipo_periodo', $tipo)->first();
    return response()->json($ciclo);

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
    $year = (int)substr($request->inicio,-4);
    $cycle = Ciclo::where('periodo', $request->periodo)->where("tipo_periodo", $request->tipo_periodo)->where('anno',$year)->first();

    //Ciclo ya existe en bd
    if (isset($cycle)){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$agregaCicloErrorPK ] );
    }

    $cond = true;
    $ciclo = new Ciclo;
    $ciclo->anno = $year;
    $ciclo->periodo = $request->periodo;
    $ciclo->tipo_periodo = $request->tipo_periodo;
    $ciclo->inicio=date_create_from_format("d/m/Y",$request->inicio)->format('Y-m-d');
    $ciclo->final=date_create_from_format("d/m/Y",$request->final)->format('Y-m-d');
    $ciclo->inicio_matricula=date_create_from_format("d/m/Y",$request->inicio_matricula)->format('Y-m-d');
    $ciclo->final_matricula=date_create_from_format("d/m/Y",$request->final_matricula)->format('Y-m-d');
    $ciclo->limite_condicion=date_create_from_format("d/m/Y",$request->limite_condicion)->format('Y-m-d');



//Modificaciones en las validaciones de las fechas -> Que fechas de inicio y final de matricula contengan a las fechas de inicio y finalización de un ciclo.
    if($ciclo->inicio > $ciclo->final) $cond = false;
    if($ciclo->inicio_matricula > $ciclo->final_matricula) $cond = false;
    if($ciclo->inicio < $ciclo->inicio_matricula) $cond = false;
	  if($ciclo->limite_condicion <  $ciclo->inicio_matricula) $cond = false;

    if ($cond == true ){
      $ciclo->save();
      return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$agregaCicloOK, 'content'=> $ciclo->toJson()]);
    }
    else
    return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$cicloErrorFechas]);
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
  public function update(Request $request){
    $year = (int)substr($request->inicio,-4);
    $cond = true;
    $ciclo = Ciclo::where('periodo', $request->periodo)->where("tipo_periodo", $request->tipo_periodo)->where('anno',$year)->first();

    if ($request->anno != $year)
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$cicloErrorEditaAnno]);

    $ciclo->periodo = $request->periodo;
    $ciclo->tipo_periodo = $request->tipo_periodo;
    $ciclo->inicio=date_create_from_format("d/m/Y",$request->inicio)->format('Y-m-d');
    $ciclo->final=date_create_from_format("d/m/Y",$request->final)->format('Y-m-d');
    $ciclo->inicio_matricula=date_create_from_format("d/m/Y",$request->inicio_matricula)->format('Y-m-d');
    $ciclo->final_matricula=date_create_from_format("d/m/Y",$request->final_matricula)->format('Y-m-d');
    $ciclo->limite_condicion=date_create_from_format("d/m/Y",$request->limite_condicion)->format('Y-m-d');


    //Modificaciones en las validaciones de las fechas -> Que fechas de inicio y final de matricula contengan a las fechas de inicio y finalización de un ciclo.
        if($ciclo->inicio > $ciclo->final) $cond = false;
        if($ciclo->inicio_matricula > $ciclo->final_matricula) $cond = false;
        if($ciclo->inicio < $ciclo->inicio_matricula) $cond = false;
    	  if($ciclo->limite_condicion <  $ciclo->inicio_matricula) $cond = false;

    if ($cond == true ){
      $ciclo->save();
      return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$cicloEditarOK, 'content'=> $ciclo->toJson()]);
    }
    else
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$cicloErrorFechas]);

  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($perido, $tipo_periodo, $anno){
    $ciclo = Ciclo::where("periodo", $perido)->where("tipo_periodo",$tipo_periodo)->where('anno',$anno)->first();
    if(isset($ciclo)){
    $ciclo->delete();
      return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$cicloEliminadoOk]);
    }
    return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$cicloErrorNoEncontrado]);
  }
}
