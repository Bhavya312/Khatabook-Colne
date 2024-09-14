<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                                'status' => 401,
                                'error' =>   'Token expired',
                            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                                'status' => 401,
                                'error' =>   'Invalid token',
                            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                                'status' => 401,
                                'error' =>   'Token is missing',
                            ], 401);
        } catch(Exception $e){
            return response()->json([
                                'status' => 500,
                                'error' =>   __('notifications.somthing_went_wrong'),
                            ], 500);
        }

        return $next($request);
    }
}
