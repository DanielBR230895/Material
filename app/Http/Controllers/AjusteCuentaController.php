<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Usuario;

use App\Messages\Mensaje;

class AjusteCuentaController extends Controller
{

  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('ajuste_cuenta');
  }
  public function update(Request $request){
    $user = Usuario::find($request->id);
    $user->Nombre = $request->Nombre;

    //si password no es vacia, cambiar password
    if($request->password!='')
    $user->password =  bcrypt($request->password);

    $user->Apellido1 = $request->Apellido1;
    $user->Apellido2 = $request->Apellido2;
    $user->Email = $request->Email;
    $user->telefono =$request->telefono;

    $user->save();
    return  response()->json([ 'status' => 'ok' ,'msg' => 'Datos editados exitosamente','content'=> $user->toJson()]);
  }

}
