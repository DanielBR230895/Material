<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;



class AsignarCondicionController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('condicion_participativa');
  }

  public function getAll()
  {
    return response()->json(Ciclo::all());
  }

  public function create()
  {
    //
  }


  public function store()
  {
  }

  public function show()
  {
    //
  }


  public function edit()
  {
    //
  }


  public function update(){

  }


  public function destroy(){
  }
}
