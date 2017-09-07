<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;


use App\Carrera;

use App\Messages\Mensaje;


class CarrerasController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('mantenimiento_carreras');

  }


  public function getAll()
  {
    return response()->json(Carrera::all());
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
    $carrera = Carrera::find($request->codigo);
    $nombre  = Carrera::where('nombre', $request->nombre)->first();

    //Error Codigo Duplicado
    if (isset($carrera))
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$agregaCarreraErrorCodigo]);


    //Error Nombre dupkicado
    if (isset($nombre))
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$carreraErrorNombre]);


    $carrera =  new Carrera;
    $carrera->codigo =  $request->codigo;
    $carrera->nombre =  $request->nombre;
    $carrera->save();
    return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$agregaCarreraOk, 'content'=> $carrera->toJson()]);

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

    $carreraEditar = Carrera::find($request->codigo);
    $carreraNombre  = Carrera::where('nombre', $request->nombre)->first();

    if (!isset($carreraEditar)){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$carreraErrorNoEncontrada]);
    }

    //si hay una carrera con el mimsmo nombre y no es la misma que se quiere editar, se esta intentado duplicar un nombre de carrera.
    if (isset($carreraNombre) && $carreraNombre != $carreraEditar){
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$carreraErrorNombre]);
    }

    $carreraEditar->nombre =  $request->nombre;
    $carreraEditar->save();

    return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$carreraEditarOK, 'content'=> $carreraEditar->toJson() ]);
  }


  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    $carrera = Carrera::find($id);
    if(!isset($carrera))
      return  response()->json([ 'status' => 'error' ,'msg' => Mensaje::$carreraErrorNoEncontrada]);

    $carrera->delete();
      return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$carreraEliminadaOK]);
  }
}
