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
        $user = $request->user;

        if($user->role != 'employee') {
            $request->user = $user;
            return $next($request);
        } else {
            return response("User doesn't have enough permissions", 401);
        }
    }
}
