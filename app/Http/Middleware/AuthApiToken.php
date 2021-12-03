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
        if($request->has('api_token')){
			$token = $request->input('api_token');
			$user = User::where('api_token', $token)->first();
			if(!$user){
				return response('Api key no vale', 401);
			} else {
				$request->usuario = $user;
				return $next($request);
			}
        } else {
                return response('No api key', 401);
        }

    }
}
