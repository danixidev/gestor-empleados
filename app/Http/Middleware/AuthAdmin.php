<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = $request->user;

            if($user->role != 'employee') {     //Comprueba que no sea un empleado
                $request->user = $user;
                return $next($request);     //Si es asi, continua y manda el usuario por el request
            } else {
                return response("User doesn't have enough permissions", 401);
            }
        } catch (\Throwable $th) {
            return response("Se ha producido un error:".$th->getMessage(), 500);
        }
    }
}
