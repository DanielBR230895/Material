<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Ciclo;

use App\Messages\Mensaje;

class HomeController extends Controller
{
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('');
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
