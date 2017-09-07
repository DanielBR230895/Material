<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Usuario;

class UsuarioController extends Controller
{

  public function index()
  {
    return view('mantenimiento_usuarios');
  }

  public function getAll()
  {
    return response()->json(Usuario::all());
  }

  public function getAllInstructores(){
    return response()->json(Usuario::where('Habilitado', true)->get());
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
  public function store(Request $request)
  {
    //revisar si ya existe un usuario con el mismo id
    $user = Usuario::find($request->id);
    if (isset($user)){
      return  response()->json([ 'status' => 'error' ,'msg' => 'Id de nuevo usuario ya existe en la base de datos']);
    }

    $user = new Usuario;
    $user->Nombre = $request->Nombre;
    $user->password =  bcrypt($request->password);
    $user->Apellido1 = $request->Apellido1;
    $user->Apellido2 = $request->Apellido2;
    $user->id = $request->id;
    $user->identificacion = $request->id;
    $user->Email = $request->Email;
    $user->Rol = $request->Rol;
    $user->telefono =$request->telefono;
    $user->Habilitado = true;
    $user->save();
    return  response()->json([ 'status' => 'ok' ,'msg' => 'Usuario creado exitosamente', 'content'=> $user->toJson()]);
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
    $user = Usuario::find($request->id);
    $user->Nombre = $request->Nombre;

    //si password no es vacia, cambiar password
    if($request->password!='')
    $user->password =  bcrypt($request->password);

    $user->Apellido1 = $request->Apellido1;
    $user->Apellido2 = $request->Apellido2;
    $user->Email = $request->Email;
    $user->Rol = $request->Rol;
    $user->telefono =$request->telefono;

    if($request->Habilitado==0 && Auth::user()->id==$request->id)
    return  response()->json([ 'status' => 'error' ,'msg' => 'El usuario no puede deshabilitarse a sí mismo']);

    $user->Habilitado = $request->Habilitado;
    $user->save();
    return  response()->json([ 'status' => 'ok' ,'msg' => 'Usuario editado exitosamente','content'=> $user->toJson()]);
  }

  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
    //
  }

}
