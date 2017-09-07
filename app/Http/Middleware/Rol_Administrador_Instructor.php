<?php

namespace App\Http\Middleware;

use Closure;

class Rol_Administrador_Instructor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
         if($request->user()->Rol != "Administrador" && $request->user()->Rol != "Instructor"){
            return redirect('/');
        }   

        return $next($request);
    }
}
