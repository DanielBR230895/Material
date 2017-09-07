<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Modulos;

use App\Messages\Mensaje;

class ModulosController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('mantenimiento_modulos');

  }


  public function getAll()
  {
    return response()->json(Modulos::all());
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
    $modulos = Modulos::find($request->codigo);
    $nombre  = Modulos::where('nombre', $request->nombre)->first();
    if (isset($modulos) || isset($nombre)){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$moduloErrorRepetido]);
    }

    $siglas = true;
    $modulos =  new Modulos;
    $modulos->codigo =  $request->codigo;
    $modulos->nombre =  $request->nombre;

    if(strlen($modulos->codigo) > 10)
      $siglas = false;

    if($siglas == true){
      $modulos->save();
      return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$agregaModuloOk, 'content'=> $modulos->toJson()]);
    }
    else
    return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$moduloErrorCodigo]);

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

    $modulosEditar = Modulos::find($request->codigo);
    $modulosNombre  = Modulos::where('nombre', $request->nombre)->first();

    if (!isset($modulosEditar)){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$moduloErrorNoEncon]);
    }

    //si hay un módulo con el mismo nombre y no es el mismo que se quiere editar, se esta intentado duplicar un nombre de módulo.
    if (isset($modulosNombre) && $modulosNombre != $modulosEditar){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$moduloErrorNombreDupli]);
    }

    $modulosEditar->nombre =  $request->nombre;
    $modulosEditar->save();

    return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$moduloEditaOk, 'content'=> $modulosEditar->toJson() ]);
  }


  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    $modulos = Modulos::find($id);
    if(!isset($modulos))
    return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$moduloErrorNoEncon]);

    $modulos->delete();
    return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$moduloEliminadoOk]);
  }
}
