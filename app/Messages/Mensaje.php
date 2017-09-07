<?php

/*En esta Clase se colocan todas los string de respuesta que
retorna el sistema , a traves de las consultas ajax*/


namespace App\Messages;

class Mensaje {

  //Mensajes de Carreras--------------------------------------------------------
  //OK
  public static $agregaCarreraOk    = 'Carrera agregada exitosamente';
  public static $carreraEditarOK    = 'Carrera editada exitosamente';
  public static $carreraEliminadaOK = 'Carrera eliminada exitosamente';

  //Error
  public static $agregaCarreraErrorCodigo = 'Código de carrera duplicado';
  public static $carreraErrorNombre       = 'Nombre de carrera duplicado';
  public static $carreraEditarErrorCodigo = 'Código de carrera no encontrado';
  public static $carreraErrorNoEncontrada = 'Código de carrera no encontrado';


  //Mensajes de Ciclos--------------------------------------------------------
  //Ok
  public static $agregaCicloOK    = 'Ciclo agregado exitosamente';
  public static $cicloEditarOK    = 'Ciclo editado exitosamente';
  public static $cicloEliminadoOk = 'Ciclo eliminado exitosamente';

  //Error
  public static $cicloErrorFechas       = 'Error en las fechas';
  public static $agregaCicloErrorPK     = 'Ya existe en la base de datos un ciclo con el mismo tipo periodo, periodo y año de fecha inicio';
  public static $cicloErrorEditaAnno    = 'El año del inicio del ciclo no se debe editar';
  public static $cicloErrorNoEncontrado = 'Ciclo no encontrado en la Base de Datos';


  //Mensajes de Matriculados (PersonasMatricula)----------------------------------
  //ok
  public static $agregaMatriculadoOk    = 'Almacenada/Almacenado exitosamente';
  public static $matriculadoEliminadoOk = 'Eliminada/Eliminado exitosamente';
  public static $matriculadoEditarOk    = 'Editada/Editado exitosamente';

  //Error
  public static $agregaMatriculadoErrorID     = 'ID ya existente en la base de datos';
  public static $matriculadoErrorNoCarrera    = 'Carrera no especificada';
  public static $matriculadoErrorNoEstudiante = 'Solo tipo Estudiantes UNA se le puede asignar Carrera';
  public static $matriculadoErrorNoEnc        = 'Matriculada/o no encontrada en la base de datos';

  //Mensajes de Modulos-----------------------------------------------------------
  //ok
  public static $agregaModuloOk     = 'Módulo agregado exitosamente';
  public static $moduloEditaOk      = 'Módulo editado exitosamente';
  public static $moduloEliminadoOk  = 'Módulo eliminado exitosamente';

  //Error
  public static $moduloErrorRepetido    = 'Ya existe un módulo con el mismo código o nombre, en la base de datos';
  public static $moduloErrorCodigo      = 'El código debe ser de 10 o menos caracteres ';
  public static $moduloErrorNoEncon     = 'Código de módulo no encontrado en la base de datos';
  public static $moduloErrorNombreDupli = 'Nombre de módulo duplicado';

  //Mensajes de Talleres-----------------------------------------------------------
  //ok
  public static $agregaTallerOk = 'Taller creado exitosamente';
  public static $tallerEditaOk  = 'Taller editado exitosamente';
  public static $tallerEliminadoOk  = 'Taller eliminado exitosamente';

  //Error
  public static $tallerErrorCodigoRepetido  = 'Ya existe un taller con el mismo código en la base de datos';
  public static $tallerErrorCodigoNoEncon   = 'Código de taller no encontrado en la base de datos';
  public static $tallerErrorNombreDupli     = 'Nombre de taller duplicado';
  public static $tallerErrorRequisito       = 'Requisito del taller referencia al mismo taller';
  public static $tallerErrorDependencias    = 'Existen ofertas cocurriculares ligadas a este taller';

  //Mensajes de Matricula-----------------------------------------------------------
  //ok
  public static $matriculaOK = 'Matrícula realizada exitosamente';
  public static $retiroOK = 'Desmatrículado exitosamente';
  public static $asignadoOK = "Asignacion participativa exitosa";

  //Error
  public static $matriculaErrorPersona   = 'No se encontró a ninguna persona con la identificación ingresada';
  public static $matriculaErrorGrupo     = 'No se encontró  ningun grupo con el código ingresado';
  public static $matriculaErrorDuplicada = 'El estudiante ya se había matriculado previamente en este grupo';
  public static $matriculaErrorNoMatriculado = 'El estudiante no aparece como matriculado en grupo de taller';
  public static $matriculaErrorCupo = 'El grupo ya alcanzó el cupo máximo de personas matriculadas';
  public static $matriculaErroRequisito = 'La persona no cumple con los requisitos para matricular este taller';


  //Mensajes de ofertas cocurriculares (grupos) -----------------------------------------------------------
  //ok
  public static $grupoEliminadoOk = 'Grupo eliminado exitosamente';
  public static $agregaGrupoOk = 'Grupo creado exitosamente';
  public static $grupoUpdateOK = 'Grupo editado exitosamente';

  //error
  public static $grupoErrorNoEncon = 'Grupo no encontrado en el sistema';
  public static $grupoErrorCupoMinMax = 'El cupo mínimo no puede ser mayor al cupo máximo de un grupo';
  public static $grupoErrorNumGrupo = 'Ya existe un grupo del mismo taller con el mismo número en la base de datos';
}
