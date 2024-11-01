<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isAuthorize = false;
        $token = $request->bearerToken();
        if(!isset($token)) { $token = $request->token; }

        // read server token
        $server_token = env('API_TOKEN', '123456');
        if(isset($token) && $token == $server_token) {
            $isAuthorize =  true;
        } else {
            $isAuthorize =  false;
        }

        if(!$isAuthorize){
            return response([
                'success' => false,
                'code' => '401',
                'message' => sprintf(config('error_code.401'), 'Vui lòng liên hệ admin')
            ], 401);
        }
        $allowedOrigins = config('constants.allwored_origins');
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;

        if (isset($allowedOrigins) && in_array($origin, $allowedOrigins)) {
            return $next($request)
                ->header('Access-Control-Allow-Origin', $origin)
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
                ->header('Access-Control-Allow-Headers', 'Content-Type');
        }

        return $next($request);
    }
}
