<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use Illuminate\Support\Facades\Auth;

use App\Usuario;


class LoginController extends Controller{

  public function authenticate(Request $request){
    if (Auth::attempt(['id' => $request->id, 'password' =>  $request->password, 'Habilitado' => 1])) {
        return redirect()->intended('/');
    }

      //consulta si el usuario  existe , pero esta deshabilitado
      $usuario = Usuario::where('id',$request->id)->where('Habilitado', 0)->first();
      if(isset($usuario))
        return back()->with('error-login', 'El usuario con el que intenta ingresar  se encuetra deshabilidato.
                                            Para más información contacte al administrador del sistema')->withInput();


      //Consulta si el usuario realmnete existe con esa Id o si la password  es incorrecta.
      $isId = Usuario::where('id',$request->id) ->get()->isEmpty();
      return back()->with('error-login', 'Credencial '.($isId?'ID':'Contraseña').' Incorrecta ')->withInput();
  }


  public function logout(Request $request){
      Auth::logout();
	  $request->session()->flush();
      return redirect('/');
  }

}
