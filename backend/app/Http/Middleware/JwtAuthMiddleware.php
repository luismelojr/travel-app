<?php

namespace App\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            
            if (!$user) {
                return ResponseHelper::unauthorized('Usuário não encontrado');
            }
            
        } catch (TokenExpiredException $e) {
            return ResponseHelper::unauthorized('Token expirado');
            
        } catch (TokenInvalidException $e) {
            return ResponseHelper::unauthorized('Token inválido');
            
        } catch (JWTException $e) {
            return ResponseHelper::unauthorized('Token não fornecido');
        }

        return $next($request);
    }
}