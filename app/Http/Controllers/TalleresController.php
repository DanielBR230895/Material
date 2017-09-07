<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Taller;

use App\Modulos;

use App\Messages\Mensaje;

class TalleresController extends Controller
{

  public function index(){
    return view('mantenimiento_talleres');
  }

  public function getAll(){
    $talleres = $this->agregarNombreRequisito( $this->agregarNombreModulo(Taller::all()) );
    return response()->json($talleres);
  }


  //Toma la coleccion de talleres y le agrega a cada elemento un atributo con el nombre del requisito;
  //Luego retorna esa coleccion.
  public function agregarNombreModulo($talleres){
    $modulos  = Modulos::all()->keyBy('codigo');

    $talleres->map(function($item, $k) use ($modulos){
      if(isset($item->modulo))
        $item->moduloNombre= $modulos[$item->modulo]->nombre;
      return $item;
    });
    return $talleres;
  }

  //Toma la coleccion de talleres y le agrega a cada elemento un atributo con el nombre del requisito
  //Luego retorna esa coleccion.
  public function agregarNombreRequisito($talleres){
    $talleresAll  = Taller::all()->keyBy('codigo');

    $talleres->map(function($item, $k) use ($talleresAll){
      if($item->requisito=="")
      return $item;
      $requisito = $talleresAll[$item->requisito];
      $item->requisitoNombre= $requisito->nombre." (".$requisito->codigo.")";
      return $item;
    });
    return $talleres;
  }



  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function getcreate()
  {
    //
  }

  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request){
    $taller = Taller::find($request->codigo);
    $tallerNombre = Taller::where('nombre', $request->nombre)->first();

    if (isset($taller)){
      return  response()->json(['status' => 'error' ,'msg' => Mensaje::$tallerErrorCodigoRepetido]);
    }

    if(isset($tallerNombre)){
      return response()->json(['status' => 'error', 'msg' => Mensaje::$tallerErrorNombreDupli]);
    }

    $taller = new Taller;
    $taller->codigo = $request->codigo;
    $taller->nombre = $request->nombre;
    $taller->descripcion = $request->textarea1;
    $taller->modulo = $request->modulo;


    if($request->requisito != ""){
      $taller->requisito = $request->requisito;
    }

    $taller->save();
    $taller->moduloNombre = Modulos::find($request->modulo)->nombre;

    //colocar el nombre del taller requisito en caso de tenerlo
    $requisito = Taller::find($request->requisito);
    $taller->requisitoNombre = isset($requisito)? $requisito->nombre." (".$requisito->codigo.")" : "";

    return response()->json(['status' => 'ok', 'msg' => Mensaje::$agregaTallerOk , 'content' => $taller->toJson()]);
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

    $tallerEditar = Taller::find($request->codigo);
    $tallerNombre = Taller::where('nombre', $request->nombre)->first();

    if (!isset($tallerEditar)){
      return  response()->json(['status' => 'error' ,'msg' => Mensaje::$tallerErrorCodigoNoEncon]);
    }

    if(isset($tallerNombre) && $tallerNombre != $tallerEditar){
      return response()->json(['status' => 'error', 'msg' => Mensaje::$tallerErrorNombreDupli]);
    }

    if($request->codigo == $request->requisito)
    return  response()->json(['status' => 'error' ,'msg' => Mensaje::$tallerErrorRequisito ]);

    $tallerEditar->nombre = $request->nombre;
    $tallerEditar->descripcion = $request->descripcion;
    $tallerEditar->modulo = $request->modulo;

    if($request->requisito != "") //si el request no tiene requisito , no se coloca para no afectar query
      $tallerEditar->requisito = $request->requisito;
    else
      $tallerEditar->requisito = null;

    $tallerEditar->save();
    $tallerEditar->moduloNombre = Modulos::find($tallerEditar->modulo)->nombre; //agregar el nombre del modulo

    //Agregar el nombre del requisito  en dado caso que exista.
    $requisito = Taller::find($request->requisito);
    $tallerEditar->requisitoNombre = (!isset($requisito)) ? ""
    :$requisito->nombre." (".$requisito->codigo.")";

    return response()->json(['status' => 'ok', 'msg' => Mensaje::$tallerEditaOk, 'content' => $tallerEditar->toJson()]);
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id){
    $taller = Taller::find($id);
    
    if(!isset($taller))
      return response()->json(['status' => 'error', 'msg' => Mensaje::$tallerErrorCodigoNoEncon]);

    try {
      $taller->delete();
      return response()->json(['status' => 'ok', 'msg' => Mensaje::$tallerEliminadoOk]);
    } catch (\Illuminate\Database\QueryException $e) {
      return response()->json(['status' => 'error', 'msg' => Mensaje::$tallerErrorDependencias]);
    }
  }
}
