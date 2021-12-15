<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AuthApiToken
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
            if($request->has('api_token')){         //Comprueba si el usuario tiene api_token
                $token = $request->input('api_token');
                $user = User::where('api_token', $token)->first();      //Si lo tiene saca el usuario y lo envia por el request
                if(!$user){
                    return response('Api token not valid', 401);
                } else {
                    $request->user = $user;
                    return $next($request);
                }
            } else {
                return response('No api token', 401);
            }
        } catch (\Throwable $th) {
            return response($th->getMessage(), 500);
        }
    }
}
