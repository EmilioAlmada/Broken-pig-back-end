<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\JwtAuth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PasswordRecoveryCheckToken
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
        $jwt = New JwtAuth();
        $token = $request->header('Authorization');
        $checkToken = $jwt->checkToken($token);
        if($checkToken){
            return $next($request);
        }

        $response = new Controller();
        return $response->forbiden('Acceso denegado');
        // response()->json($response->forbidden(),$response->forbidden()['code']);
    }
}

