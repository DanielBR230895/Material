<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Persona_Matricula;

use App\Carrera;

use App\Messages\Mensaje;

class PersonasMatriculaController extends Controller
{

  const tipos = array( "AM" =>'Adulto Mayor', 'A'=>'Autorización', 'C'=>'Comunidad', 'EU'=>'Estudiante UNA', "F"=>"Funcionario");
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('mantenimiento_matriculados');
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
  * Retorna todos los personas matriculadas
  *
  * @param
  * @return Json con matriculados
  */
  public function getAll()
  {
    $matriculados = $this->agregarNombreTipoP($this->agregarNombreCarrera(Persona_Matricula::all()));
    return response()->json( $matriculados );
  }


  /**
  * Retorna todos los datos de un matriculado especifico
  *
  * @param  $id : identificacion de matriculado
  * @return Json de matriculado
  */
  public function getMAtriculado($id = '')
  {
    try{
      $matriculado  = Persona_Matricula::find($id);
      return isset($matriculado) ? $matriculado->tojson():'{}';
      }catch(\Exception $ex){
        return '{}';
        }
      }


      //Toma la coleccion de personas_matriculadas y le agrega a cada elemento un atributo con el nombre de la carrera;
      //Luego retorna esa coleccion.
      public function agregarNombreCarrera($personas_matriculadas){
        $carreras  = Carrera::all()->keyBy('codigo');

        $personas_matriculadas->map(function($item, $k) use ($carreras){
          if(isset($carreras[$item->carrera]))
          $item->carreraNombre= $carreras[$item->carrera]->nombre;
          return $item;
        });
        return $personas_matriculadas;
      }

      //Toma la coleccion de personas_matriculadas y le agrega a cada elemento un atributo con el nombre del tipo de persona;
      //Luego retorna esa coleccion.
      public function agregarNombreTipoP($personas_matriculadas){
        $tipos = self::tipos;
        $personas_matriculadas->map(function($item, $k) use ($tipos){
          $item->tipo_personaNombre= $tipos[$item->tipo_persona];
          return $item;
        });
        return $personas_matriculadas;
      }


      /**
      * Store a newly created resource in storage.
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public function store(Request $request)
      {
        //revisar si ya existe una persona con el mismo id
        $new_persona = Persona_Matricula::find($request->identificacion);
        if (isset($new_persona)){
          return  response()->json([ 'status' => 'error' ,
          'msg' => Mensaje::$agregaMatriculadoErrorID]);
        }

        $new_persona = new Persona_Matricula;
        $new_persona->identificacion = $request->identificacion;
        $new_persona->nombre = $request->nombre;
        $new_persona->apellido1 = $request->apellido1;
        $new_persona->apellido2 = $request->apellido2;
        $new_persona->genero = $request->genero;
        $new_persona->email = $request->email;
        $new_persona->residencia = $request->residencia;
        $new_persona->telefono = $request->telefono;
        $new_persona->tipo_persona = $request->tipo_persona;

        //validacion carrera
        if($request->tipo_persona=="EU" && $request->carrera=="")
          return response()->json([ 'status' => 'error' ,'msg' => Mensaje::$matriculadoErrorNoCarrera]);

        elseif($request->tipo_persona!="EU" && $request->carrera!="")
          return response()->json([ 'status' => 'error' ,'msg' => Mensaje::$matriculadoErrorNoEstudiante]);

        elseif($request->tipo_persona=="EU" && $request->carrera != "")
          $new_persona->carrera = $request->carrera;
        //------------------

        $new_persona->fecha_nacimiento=date_create_from_format("d/m/Y",$request->fecha_nacimiento)->format('Y-m-d');
        $new_persona->save();

	      $new_persona->tipo_personaNombre= $this::tipos[$new_persona->tipo_persona];
        return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$agregaMatriculadoOk,
        'content'=> $new_persona->toJson()]);
      }


      /**
      * Este metodo es llamado cuando se desea agregar una persona y matricularla simultaneamente
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public static function storeFromMatricula(Request $request)
      {


        $new_persona = new Persona_Matricula;
        $new_persona->identificacion = $request->persona_matricula;
        $new_persona->nombre = $request->nombre;
        $new_persona->apellido1 = $request->apellido1;
        $new_persona->apellido2 = $request->apellido2;
        $new_persona->genero = $request->genero;
        $new_persona->email = $request->email;
        $new_persona->residencia = $request->residencia;
        $new_persona->telefono = $request->telefono;
        $new_persona->tipo_persona = $request->tipo_persona;
        $new_persona->carrera = $request->carrera;

        $new_persona->fecha_nacimiento=date_create_from_format("d/m/Y",$request->fecha_nacimiento)->format('Y-m-d');
        $new_persona->save();

        return  true;
      }



 /**
      * Este metodo es llamado cuando se matricula un usuario para asi actualizar 
      * si  dato tipo_persona
      *
      * @param  \Illuminate\Http\Request  $request
      * @return \Illuminate\Http\Response
      */
      public static function UpdateTipoPersonaFromMatricula(Request $request)
      {

        $persona = Persona_Matricula::find($request->persona_matricula);
        
        //si la persona no existe, no se realiza ningun cambio. 
        if (!isset($persona)) 
          return false;
        
        $persona->tipo_persona = $request->tipo_persona;

        if($request->tipo_persona=="EU" && $request->carrera != "")
          $persona->carrera = $request->carrera;

        else
          $persona->carrera = null;

        $persona->save();

        return  true;
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
        $persona = Persona_Matricula::find($request->identificacion);

        $persona->nombre = $request->nombre;
        $persona->apellido1 = $request->apellido1;
        $persona->apellido2 = $request->apellido2;
        $persona->genero = $request->genero;
        $persona->email = $request->email;
        $persona->residencia = $request->residencia;
        $persona->telefono = $request->telefono;
        $persona->tipo_persona = $request->tipo_persona;

        //validacion carrera------------------
        if($request->tipo_persona=="EU" && $request->carrera=="")
          return response()->json([ 'status' => 'error' ,'msg' => Mensaje::$matriculadoErrorNoCarrera]);

        elseif($request->tipo_persona!="EU" && $request->carrera!="")
          return response()->json([ 'status' => 'error' ,'msg' => Mensaje::$matriculadoErrorNoEstudiante]);

        elseif($request->tipo_persona=="EU" && $request->carrera != "")
          $persona->carrera = $request->carrera;

        else
         $persona->carrera = null;
        //------------------------------------

        $persona->fecha_nacimiento=date_create_from_format("d/m/Y",$request->fecha_nacimiento)->format('Y-m-d');
        $persona->save();

        //agregar el nombre de la carrera, si es estudiante una
        $persona->carreraNombre =$request->tipo_persona=="EU" ?
        Carrera::find($request->carrera)->nombre:
        null;


        //agregar nombre tipo persona
        $persona->tipo_personaNombre= $this::tipos[$persona->tipo_persona];

        return  response()->json([ 'status' => 'ok' ,'msg' => Mensaje::$matriculadoEditarOk,'content'=> $persona->toJson()]);
      }

      /**
      * Remove the specified resource from storage.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */
      public function destroy($id)
      {
        $persona = Persona_Matricula::find($id);
        if(!isset($persona))
        return response()->json(['status' => 'error', 'msg' => Mensaje::$matriculadoErrorNoEnc]);

        $persona->delete();
        return response()->json(['status' => 'ok', 'msg' => Mensaje::$matriculadoEliminadoOk]);
      }
    }
