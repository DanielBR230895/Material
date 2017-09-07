<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ComprobanteMatricula extends Mailable
{
     use Queueable, SerializesModels;


    public $id;
    public $nombre;
    public $apellido1;
    public $apellido2;
    public $taller;
    public $grupo;
    public $instructor;
    public $lugar;
    public $horario;
    public $matriculador;
    public $anno;
    public $tipo_periodo;
    public $periodo;
    public $correoInstructor;
    public $inicioL;
    public $fechaF;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id, $nombre, $apellido1, $apellido2, $taller, $grupo, $instructor,
                                $lugar, $horario,$matriculador,$anno, $tipo_periodo, $periodo,$correoInstructor,$inicioL,$fechaF)
    {
      $this->id=$id;
      $this->nombre=$nombre;
      $this->apellido1=$apellido1;
      $this->apellido2=$apellido2;
      $this->taller=$taller;
      $this->grupo=$grupo;
      $this->instructor=$instructor;
      $this->lugar=$lugar;
      $this->horario=$horario;
      $this->matriculador=$matriculador;
      $this->anno = $anno;
      $this->tipo_periodo = $tipo_periodo;
      $this->periodo = $periodo;
      $this->correoInstructor =$correoInstructor ;
      $this->inicioL = $inicioL;
      $this->fechaF = $fechaF;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
      //date_default_timezone_set('America/Costa_Rica');
      return $this->view('comprobanteMatriculaView2')
                  ->subject('Matrícula de Taller UNA - '.$this->taller);
    }
}
