<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use DB;

use PDF;

use App\Ciclo;

use DateTime;

use App\Carrera;

use App\Usuario;

use App\Taller;

use App\OfertaCocurricular;


class InformesController extends Controller
{

  const tipos = array( "AM" =>'Adulto Mayor', 'A'=>'Autorización', 'C'=>'Comunidad', 'EU'=>'Estudiante UNA', "F"=>"Funcionario");
  const condiciones = array('SA' => 'Sin Asignar', 'S' => 'Satisfactoria', 'I' => 'Insatisfactoria', 'N' => 'NSP');

  /**
  * Display a listing of the resource.
  *
  *@param  string periodo      ->datos del ciclo
  *@param  string anno        ->datos del ciclo
  *@param  string tipo_periodo ->datos del ciclo
  *
  *
  * @return string json todas las personas matriculadas en ciclo especificado
  */


  public function index()
  {
    return view('informes');
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

  public function getCicloConFechas($periodo='',$anno='', $tipo_periodo='' ){
    $Ciclo = Ciclo::where('periodo', $periodo)->where("tipo_periodo", $tipo_periodo)
    ->where('anno',$anno)->first();

    $Ciclo->inicio_matricula = date_create_from_format('Y-m-d', $Ciclo->inicio_matricula)->format('d/m/Y');
    $Ciclo->final_matricula = date_create_from_format('Y-m-d', $Ciclo->final_matricula)->format('d/m/Y');
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    return $Ciclo;
  }



  //-----------------------------------------------------------------------------------------------------------------
  //1ER INFORME -> MATRICULADOS POR CICLO
  //-----------------------------------------------------------------------------------------------------------------

  public function getInformeMatriculadosCiclo($periodo='',$anno='', $tipo_periodo='' ){


    //PM -> personas matriculadas, M->id de personas matriculas en ciclo
    $Personas = DB::select('select PM.*, M.talleres
    from personas__matricula as PM,
    ( select matriculas.persona_matricula,
    GROUP_CONCAT(OCT.tallerNombre, \' Grupo: \', OCT.numeroGrupo SEPARATOR \' \n\') as talleres
    from matriculas,
    (select OC.grupo, OC.numeroGrupo,
    T.nombre as tallerNombre
    from talleres as T,
    (select  grupo, numeroGrupo, taller
    from oferta_cocurriculares
    where periodo_c = :periodo AND anno_c = :anno
    AND tipo_periodo_c = :tipo_periodo
    ) as OC
    where T.codigo = OC.taller
    ) as OCT
    where  matriculas.grupo = OCT.grupo
    group by matriculas.persona_matricula
    ) as M
    where PM.identificacion = M.persona_matricula
    order by PM.apellido1',
    ['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo]);


    //Agrega nombre ed tipo de persona
    $Personas =$this->agregarNombreCarrera($this->agregarNombreTipoP(collect($Personas)));

    //obtener informacion de ciclo
    $Ciclo = $this->getCicloConFechas($periodo,$anno, $tipo_periodo);


    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Personas' => $Personas,
      'Ciclo'  => $Ciclo,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_matriculados_ciclo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_matriculados_ciclo.pdf');

  }


  //-----------------------------------------------------------------------------------------------------------------
  //2DO INFORME -> CARRERAS POR CICLO    ///EIRCK
  //-----------------------------------------------------------------------------------------------------------------


  public function getInformeCarrerasCiclo($periodo='',$anno='', $tipo_periodo='',$carrera_codigo='' ){

    //PM -> personas matriculadas, M->id de personas matriculas en ciclo
    $Personas = DB::select('select PM.*
    from personas__matricula as PM,
    ( select distinct matriculas.persona_matricula
    from matriculas,
    (select  grupo
    from oferta_cocurriculares
    where periodo_c = :periodo AND anno_c = :anno
    AND tipo_periodo_c = :tipo_periodo)  as OC
    where  matriculas.grupo = OC.grupo AND matriculas.carrera = :carrera
    ) as M
    where PM.identificacion = M.persona_matricula
    order by PM.apellido1',
    ['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo, 'carrera' => $carrera_codigo]);


    //obtener informacion de ciclo
    $Ciclo = Ciclo::where('periodo', $periodo)->where("tipo_periodo", $tipo_periodo)
    ->where('anno',$anno)->first();
    $Ciclo->inicio_matricula = date_create_from_format('Y-m-d', $Ciclo->inicio_matricula)->format('d/m/Y');
    $Ciclo->final_matricula = date_create_from_format('Y-m-d', $Ciclo->final_matricula)->format('d/m/Y');
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    //Obtener informacion sobre Carrera
    $Carrera = Carrera::where('codigo', $carrera_codigo)->first();

    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Personas' => $Personas,
      'Ciclo'  => $Ciclo,
      'Carrera' =>$Carrera,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_carreras_ciclo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_carrera_ciclo.pdf');

  }



  //-----------------------------------------------------------------------------------------------------------------
  //3ER INFORME -> ECONÓMICO POR CICLO Y Grupo     ERICK
  //-----------------------------------------------------------------------------------------------------------------


  public function getInformeEconomicoGrupo($grupo=''){
    $Personas = DB::select('select T1.numReciboFUNDAUNA, identificacion, T1.tipo_persona, nombre, apellido1, apellido2 from personas__matricula, (select numReciboFUNDAUNA, persona_matricula, tipo_persona from matriculas where grupo = :elGrupo) as T1 where T1.persona_matricula = identificacion', ['elGrupo' => $grupo]);

    $Grupo = OfertaCocurricular::where('grupo', $grupo)->first();

    //obtener informacion de ciclo
    $Ciclo = Ciclo::where('periodo', $Grupo->periodo_c)->where("tipo_periodo", $Grupo->tipo_periodo_c)
    ->where('anno',$Grupo->anno_c)->first();
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    $Taller = Taller::where('codigo', $Grupo->taller)->first();
    $Instructor = Usuario::where('identificacion', $Grupo->idInstructor)->first();

    if(isset($Instructor))
   {
     $Ciclo->Nombre = $Instructor->Nombre;
     $Ciclo->Apellido1 = $Instructor->Apellido1;
     $Ciclo->Apellido2 = $Instructor->Apellido2;

   }
   else
   {
     $Ciclo->Nombre = '*Pendiente de asignar';
     $Ciclo->Apellido1 = ' ';
     $Ciclo->Apellido2 = ' ';
   }



    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Ciclo'  => $Ciclo,
      'Taller' => $Taller,
      'Costos' => $Grupo,
      'Personas' => $Personas,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_Economico_Grupo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_Economico_Grupo.pdf');
  }




  //-----------------------------------------------------------------------------------------------------------------
  //4TO INFORME -> ECONÓMICO DETALLADO
  //-----------------------------------------------------------------------------------------------------------------
public function getInformeEconomicoDetallado($periodo='',$anno='', $tipo_periodo=''){
    //PM -> personas matriculadas, M->id de personas matriculas en ciclo
    $grupos = DB::select('select OC.grupo,OC.taller, OC.numeroGrupo, OC.idInstructor, OC.costoComunidad,
     OC.costoAdultoMayor, OC.costoFuncionario, OC.costoEstudiante, OC.lugar, OC.horario,
    T.nombre as nombreTaller,
    M.nombre as nombreModulo,
    COUNT(Ma.tipo_persona) as totalPersonas,
    SUM(IF(Ma.tipo_persona = \'C \', 1, 0)) AS comunidad,
    SUM(IF(Ma.tipo_persona = \'F \', 1, 0)) AS funcionario,
    SUM(IF(Ma.tipo_persona = \'AM\', 1, 0)) AS adulto,
    SUM(IF(Ma.tipo_persona = \'EU\', 1, 0)) AS estudianteU
    from oferta_cocurriculares as OC
    inner join talleres as T on OC.taller = T.codigo
    inner join  modulos as M on T.modulo = M.codigo
    inner join matriculas as Ma on Ma.grupo = OC.grupo
    where OC.anno_c = :anno
    AND OC.periodo_c= :periodo
    AND OC.tipo_periodo_c = :tipo_periodo
    GROUP BY OC.grupo,OC.taller, OC.numeroGrupo, OC.idInstructor, OC.costoComunidad,
             OC.costoAdultoMayor, OC.costoFuncionario, OC.costoEstudiante, OC.lugar, OC.horario,
             nombreTaller, nombreModulo
    order by M.nombre, T.nombre, OC.numeroGrupo;',
    ['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo]);

    $totalCiclo = 0;

    foreach($grupos as $grupo){
      $totalGrupo = $grupo->costoComunidad   * $grupo->comunidad
                   +$grupo->costoFuncionario * $grupo->funcionario
                   +$grupo->costoAdultoMayor * $grupo->adulto
                   +$grupo->costoEstudiante  * $grupo->estudianteU;

      $grupo->totalRecaudado = '¢ '.$totalGrupo;

      $instructor = Usuario::find($grupo->idInstructor);
      if(isset($instructor))
        $grupo->nombreInstructor = $instructor->Nombre." ".$instructor->Apellido1
                                    ." ".$instructor->Apellido2." (ID: ".$grupo->idInstructor.") ";
      else
        $grupo->nombreInstructor = "Sin asignar";

      $grupo->desgloceComunidad     = $grupo->comunidad.' Comunidad : ¢ '         .$grupo->comunidad*$grupo->costoComunidad;
      $grupo->desgloceFuncionarios  = $grupo->funcionario.' Funcionarios UNA: ¢ ' .$grupo->funcionario*$grupo->costoFuncionario;
      $grupo->desgloceAdultos       = $grupo->adulto.' Adultos Mayores: ¢ '       .$grupo->adulto*$grupo->costoAdultoMayor;
      $grupo->desgloceEstudiantesU  = $grupo->estudianteU.' Estudiantes UNA: ¢ '  .$grupo->estudianteU*$grupo->costoEstudiante;

      $totalCiclo = $totalCiclo + $totalGrupo;
    }



    //obtener informacion de ciclo
    $Ciclo = $this->getCicloConFechas($periodo,$anno, $tipo_periodo);

    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'grupos' => $grupos,
      'Ciclo'  => $Ciclo,
      'hoy'  => $hoy->format('d/m/Y H:i:s'),
      'totalCiclo' => $totalCiclo
    ];

    $pdf = PDF::loadView('Informe_economico_detallado', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_economico_detallado(alt).pdf');
  }


  //-----------------------------------------------------------------------------------------------------------------
  //5TO INFORME -> ECONÓMICO GENERAL
  //-----------------------------------------------------------------------------------------------------------------


  public function getInformeEconomicoGeneral($periodo='',$anno='', $tipo_periodo=''){

//PM -> personas matriculadas, M->id de personas matriculas en ciclo
$grupos = DB::select('select OC.*,
T.nombre as nombreTaller,
M.nombre as nombreModulo
from oferta_cocurriculares as OC
inner join talleres as T on OC.taller = T.codigo
inner join  modulos as M on T.modulo = M.codigo
where OC.anno_c = :anno
AND OC.periodo_c= :periodo
AND OC.tipo_periodo_c = :tipo_periodo
order by M.nombre, T.nombre, OC.numeroGrupo',
['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo]);

$totalCiclo = 0;
$RecaudadoComunidad = 0;
$RecaudadoFuncionario = 0;
$RecaudadoAdultMayor = 0;
$RecaudadoEstudiante = 0;

foreach($grupos as $grupo){
  $MatriculadosC =  DB::select('select SUM(IF(tipo_persona = \'C \', 1, 0)) AS comunidad,
  SUM(IF(tipo_persona = \'F \', 1, 0)) AS funcionario,
  SUM(IF(tipo_persona = \'AM\', 1, 0)) AS adulto,
  SUM(IF(tipo_persona = \'EU\', 1, 0)) AS estudianteU
  FROM   matriculas
  WHERE  grupo = :grupo ',
  ['grupo' => $grupo->grupo]);

  $grupo->totalPersonas = $MatriculadosC[0]->comunidad
                          +$MatriculadosC[0]->funcionario
                          +$MatriculadosC[0]->adulto
                          +$MatriculadosC[0]->estudianteU;


  $totalGrupo = $grupo->costoComunidad * $MatriculadosC[0]->comunidad
  +$grupo->costoFuncionario * $MatriculadosC[0]->funcionario
  +$grupo->costoAdultoMayor * $MatriculadosC[0]->adulto
  +$grupo->costoEstudiante * $MatriculadosC[0]->estudianteU;

  $totalCiclo = $totalCiclo + $totalGrupo;
  $RecaudadoComunidad = $RecaudadoComunidad + $grupo->costoComunidad * $MatriculadosC[0]->comunidad;
  $RecaudadoFuncionario = $RecaudadoFuncionario + $grupo->costoFuncionario * $MatriculadosC[0]->funcionario;
  $RecaudadoAdultMayor = $RecaudadoAdultMayor   + $grupo->costoAdultoMayor * $MatriculadosC[0]->adulto;
  $RecaudadoEstudiante = $RecaudadoEstudiante   + $grupo->costoEstudiante * $MatriculadosC[0]->estudianteU;

}
    $RecaudadoTotal = $totalCiclo;
    $RecaudadoTotalTemp = (($RecaudadoComunidad) + ( $RecaudadoFuncionario ) +
    ( $RecaudadoAdultMayor ) + ($RecaudadoEstudiante) ) ;
    $RecaudadoTotal =  '¢ '.$RecaudadoTotalTemp;
    $RecaudadoComunidad = '¢ '.$RecaudadoComunidad;
    $RecaudadoFuncionario = '¢ '.$RecaudadoFuncionario;
    $RecaudadoAdultMayor = '¢ '.$RecaudadoAdultMayor;
    $RecaudadoEstudiante = '¢ '.$RecaudadoEstudiante;

    $Ciclo = Ciclo::where('periodo', $periodo)->where("tipo_periodo", $tipo_periodo)
    ->where('anno',$anno)->first();

    $Ciclo->inicio_matricula = date_create_from_format('Y-m-d', $Ciclo->inicio_matricula)->format('d/m/Y');
    $Ciclo->final_matricula = date_create_from_format('Y-m-d', $Ciclo->final_matricula)->format('d/m/Y');
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');


    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'RecaudadoAdultMayor' => $RecaudadoAdultMayor,
      'RecaudadoComunidad' => $RecaudadoComunidad,
      'RecaudadoFuncionario' => $RecaudadoFuncionario,
      'RecaudadoEstudiante' => $RecaudadoEstudiante,
      'RecaudadoTotal' => $RecaudadoTotal,
      'Ciclo'  => $Ciclo,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_economico_general', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_economico_general.pdf');

  }




  //-----------------------------------------------------------------------------------------------------------------
  //6TO INFORME -> CONDICIÓN PARTICIPATIVA POR CICLO Y GRUPO
  //-----------------------------------------------------------------------------------------------------------------

  public function getInformeCondicionParticipativaCiclo($grupo=''){
    //PM -> personas matriculadas, M->id de personas matriculas en ciclo

    $Personas = DB::select('select T1.calificacion, identificacion, T1.tipo_persona, nombre, apellido1, apellido2, T1.carrera
    from personas__matricula,
    (select calificacion, persona_matricula, tipo_persona,carrera
    from matriculas where grupo = :elGrupo) as T1
    where T1.persona_matricula = identificacion',
    ['elGrupo' => $grupo]);

    //Agrega nombre ed tipo de persona
    $Personas =$this->agregarNombreCarrera($this->agregarNombreTipoP(collect($Personas)));

    //Agregar la condición de persona
    $Personas =$this->agregarCondicion(collect($Personas));

    //Obtener el grupo con que se va a trabajar
    $Grupo = OfertaCocurricular::where('grupo', $grupo)->first();

    //obtener informacion de ciclo
    $Ciclo = Ciclo::where('periodo', $Grupo->periodo_c)->where("tipo_periodo", $Grupo->tipo_periodo_c)
    ->where('anno',$Grupo->anno_c)->first();
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    //Agregando código del taller
    $Taller = Taller::where('codigo', $Grupo->taller)->first();

    //Agregando información del instructor de ese taller
    $Instructor = Usuario::where('identificacion', $Grupo->idInstructor)->first();

    if(isset($Instructor))
   {
     $Ciclo->Nombre = $Instructor->Nombre;
     $Ciclo->Apellido1 = $Instructor->Apellido1;
     $Ciclo->Apellido2 = $Instructor->Apellido2;

   }
   else
   {
     $Ciclo->Nombre = '*Pendiente de asignar';
     $Ciclo->Apellido1 = ' ';
     $Ciclo->Apellido2 = ' ';
   }


    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Personas'   => $Personas,
      'Ciclo'      => $Ciclo,
      'Costos'      => $Grupo,
      'Taller'     => $Taller,
      'hoy'        => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('informe_condicion_participativa_ciclo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('informe_condicion_participativa_ciclo.pdf');

  }

  public function agregarCondicion($Condicion){
    $condiciones = self::condiciones;
    $Condicion->map(function($item, $k) use ($condiciones){
      $item->calificaciones= $condiciones[$item->calificacion];
      return $item;
    });
    return $Condicion;
  }




  //-----------------------------------------------------------------------------------------------------------------
  //7MO INFORME -> PERSONAS POR GRUPO
  //-----------------------------------------------------------------------------------------------------------------


  public function getInformePersonasGrupo($grupo=''){
    $Personas = DB::select('  select identificacion, nombre, apellido1, apellido2,email,telefono,T1.tipo_persona,T1.carrera from personas__matricula,
    (select  persona_matricula,tipo_persona,carrera from matriculas where grupo = :elGrupo) as T1 where T1.persona_matricula = identificacion', ['elGrupo' => $grupo]);

    //Agrega a cada persona, el nombre de la carrera a la que pertenece
    $Personas = $this->agregarNombreCarrera(collect($Personas));

    $Grupo = OfertaCocurricular::where('grupo', $grupo)->first();

    //obtener informacion de ciclo
    $Ciclo = Ciclo::where('periodo', $Grupo->periodo_c)->where("tipo_periodo", $Grupo->tipo_periodo_c)
    ->where('anno',$Grupo->anno_c)->first();
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    $Taller = Taller::where('codigo', $Grupo->taller)->first();
    $Instructor = Usuario::where('identificacion', $Grupo->idInstructor)->first();

     if(isset($Instructor))
    {
      $Ciclo->Nombre = $Instructor->Nombre;
      $Ciclo->Apellido1 = $Instructor->Apellido1;
      $Ciclo->Apellido2 = $Instructor->Apellido2;

    }
    else
    {
      $Ciclo->Nombre = '*Pendiente de asignar';
      $Ciclo->Apellido1 = ' ';
      $Ciclo->Apellido2 = ' ';
    }

    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Ciclo'  => $Ciclo,
      'Taller' => $Taller,
      'Personas' => $Personas,
      'Grupo' =>$Grupo,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_Personas_Grupo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_Personas_Grupo.pdf');
  }

 //-----------------------------------------------------------------------------------------------------------------
  //8MO INFORME -> CANTIDAD PERSONAS POR CICLO DE UNA CARRERA   ERICK
  //-----------------------------------------------------------------------------------------------------------------

  public function getInformeCantidadCarrerasCiclo($periodo='',$anno='', $tipo_periodo='' ){

    //PM -> personas matriculadas, M->id de personas matriculas en ciclo T1-> cantidad y codigo de carrera
    $Personas = DB::select('select count(T1.carreras) as cantidad, carreras.codigo, carreras.nombre from carreras,
    (select M.carreras
    from personas__matricula as PM,
    ( select distinct matriculas.persona_matricula, matriculas.carrera as carreras
    from matriculas,
    (select  grupo
    from oferta_cocurriculares
    where periodo_c = :periodo AND anno_c = :anno
    AND tipo_periodo_c = :tipo_periodo)  as OC
    where  matriculas.grupo = OC.grupo
    ) as M
    where PM.identificacion = M.persona_matricula) as T1
    where T1.carreras = carreras.codigo
    group by carreras.codigo, carreras.nombre
    ',
    ['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo]);


    //obtener informacion de ciclo
    $Ciclo = Ciclo::where('periodo', $periodo)->where("tipo_periodo", $tipo_periodo)
    ->where('anno',$anno)->first();
    $Ciclo->inicio_matricula = date_create_from_format('Y-m-d', $Ciclo->inicio_matricula)->format('d/m/Y');
    $Ciclo->final_matricula = date_create_from_format('Y-m-d', $Ciclo->final_matricula)->format('d/m/Y');
    $Ciclo->inicio = date_create_from_format('Y-m-d', $Ciclo->inicio)->format('d/m/Y');
    $Ciclo->final = date_create_from_format('Y-m-d', $Ciclo->final)->format('d/m/Y');

    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'Personas' => $Personas,
      'Ciclo'  => $Ciclo,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_Cantidad_Personas_Carrera', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_Cantidad_Personas_Carrera.pdf');

  }



  //-----------------------------------------------------------------------------------------------------------------
  //9NO INFORME -> CANTIDAD PERSONAS POR GÉNERO POR CICLO
  //-----------------------------------------------------------------------------------------------------------------

  public function getInformeGeneroCiclo($periodo='',$anno='', $tipo_periodo=''){

      $MatriculadosGEN = DB::select('select
      COUNT(Ma.tipo_persona) as totalPersonas,
      SUM(IF(PM.genero= \'F \', 1, 0)) AS TotalF,
      SUM(IF(PM.genero= \'M \', 1, 0)) AS TotalM,
      SUM(IF(Ma.tipo_persona = \'C \'  and PM.genero= \'F \', 1, 0)) AS comunidadF,
      SUM(IF(Ma.tipo_persona = \'C \'  and PM.genero= \'M \', 1, 0)) AS comunidadM,
      SUM(IF(Ma.tipo_persona = \'F \'  and PM.genero= \'F \', 1, 0)) AS funcionarioF,
      SUM(IF(Ma.tipo_persona = \'F \'  and PM.genero= \'M \', 1, 0)) AS funcionarioM,
      SUM(IF(Ma.tipo_persona = \'AM \'  and PM.genero= \'F \', 1, 0)) AS adultoF,
      SUM(IF(Ma.tipo_persona = \'AM \'  and PM.genero= \'M \', 1, 0)) AS adultoM,
      SUM(IF(Ma.tipo_persona = \'EU \'  and PM.genero= \'F \', 1, 0)) AS estudianteUF,
      SUM(IF(Ma.tipo_persona = \'EU \'  and PM.genero= \'M \', 1, 0)) AS estudianteUM
      from oferta_cocurriculares as OC
      inner join matriculas as Ma on Ma.grupo = OC.grupo
      inner join personas__matricula as PM on PM.identificacion = Ma.persona_matricula
      where OC.anno_c = :anno
      AND OC.periodo_c= :periodo
      AND OC.tipo_periodo_c = :tipo_periodo',
    ['periodo' => $periodo, 'anno' => $anno, 'tipo_periodo' => $tipo_periodo]);

    foreach($MatriculadosGEN as $grupo){

      $grupo->totalPersonas = $MatriculadosGEN[0]->totalPersonas;
      $grupo->totalMas = $MatriculadosGEN[0]->TotalM;
      $grupo->totalFem = $MatriculadosGEN[0]->TotalF;
      $grupo->totalGenero = $MatriculadosGEN[0]->TotalF + $MatriculadosGEN[0]->TotalM;

      $grupo->totalMasc = ' Masculino: '.$MatriculadosGEN[0]->TotalM;
      $grupo->totalFeme = ' Femenino: '.$MatriculadosGEN[0]->TotalF;

      $grupo->desgloseComunidadM = ' Comunidad: '.$MatriculadosGEN[0]->comunidadM;
      $grupo->desgloseFuncionariosM = ' Funcionarios UNA: '.$MatriculadosGEN[0]->funcionarioM;
      $grupo->desgloseAdultosM = ' Adultos Mayores: '.$MatriculadosGEN[0]->adultoM;
      $grupo->desgloseEstudiantesUM = ' Estudiantes UNA: '.$MatriculadosGEN[0]->estudianteUM;

      $grupo->desgloseComunidadF = ' Comunidad: '.$MatriculadosGEN[0]->comunidadF;
      $grupo->desgloseFuncionariosF = ' Funcionarios UNA: '.$MatriculadosGEN[0]->funcionarioF;
      $grupo->desgloseAdultosF = ' Adultas Mayores: '.$MatriculadosGEN[0]->adultoF;
      $grupo->desgloseEstudiantesUF = ' Estudiantes UNA: '.$MatriculadosGEN[0]->estudianteUF;

    }


    //obtener informacion de ciclo
    $Ciclo = $this->getCicloConFechas($periodo,$anno, $tipo_periodo);

    date_default_timezone_set('America/Costa_Rica');
    $hoy = new DateTime();

    $data = [
      'grupos' => $MatriculadosGEN,
      'Ciclo'  => $Ciclo,
      'hoy'  => $hoy->format('d/m/Y H:i:s')
    ];

    $pdf = PDF::loadView('Informe_Genero_Ciclo', $data)->setPaper('a4', 'landscape');
    return $pdf->download('Informe_Genero_Ciclo.pdf');

    }


}
