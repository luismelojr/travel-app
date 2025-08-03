<?php

namespace App\Services;

use App\Contracts\AuthServiceInterface;
use App\Enums\UserRoleEnum;
use App\Exceptions\ApiException;
use App\Exceptions\AuthException;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * Serviço de autenticação
 * 
 * Responsável por toda lógica de negócio relacionada à autenticação,
 * incluindo registro, login, logout, refresh de token e dados do usuário
 */
class AuthService implements AuthServiceInterface
{
    /**
     * Registra um novo usuário no sistema
     * 
     * Valida se o email não existe, cria o usuário com role USER,
     * gera token JWT e registra log de sucesso
     * 
     * @param array $data Dados do usuário (name, email, password)
     * @return AuthResource Dados do usuário e token de autenticação
     * @throws ApiException Quando email já existe
     */
    public function register(array $data): AuthResource
    {
        // Verificar se email já existe
        if (User::where('email', $data['email'])->exists()) {
            Log::warning('Tentativa de registro com email duplicado', [
                'email' => $data['email'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            throw ApiException::conflict('Email já cadastrado no sistema');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => UserRoleEnum::USER,
        ]);

        $token = JWTAuth::fromUser($user);

        Log::info('Usuário registrado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip()
        ]);

        return new AuthResource([
            'user' => $user->fresh(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }

    /**
     * Autentica um usuário no sistema
     * 
     * Valida credenciais, gera token JWT e registra logs de segurança
     * 
     * @param array $credentials Credenciais de login (email, password)
     * @return AuthResource Dados do usuário e token de autenticação
     * @throws AuthException Quando credenciais são inválidas
     */
    public function login(array $credentials): AuthResource
    {
        if (!$token = JWTAuth::attempt($credentials)) {
            Log::warning('Tentativa de login inválida', [
                'email' => $credentials['email'],
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
            
            throw AuthException::invalidCredentials();
        }

        $user = auth('api')->user();

        Log::info('Login realizado com sucesso', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => request()->ip()
        ]);

        return new AuthResource([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }

    /**
     * Renova o token de autenticação
     * 
     * Invalida o token atual e gera um novo com nova expiração
     * 
     * @return AuthResource Novo token e dados do usuário
     * @throws AuthException Quando token está expirado ou inválido
     */
    public function refresh(): AuthResource
    {
        try {
            $token = JWTAuth::refresh();
            $user = auth('api')->user();

            return new AuthResource([
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config('jwt.ttl') * 60
            ]);
        } catch (JWTException $e) {
            throw AuthException::tokenExpired();
        }
    }

    /**
     * Efetua logout invalidando o token atual
     * 
     * Remove o token do usuário da blacklist do JWT
     * 
     * @return void
     * @throws AuthException Quando token é inválido
     */
    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            throw AuthException::invalidToken();
        }
    }

    /**
     * Retorna dados do usuário autenticado
     * 
     * Busca dados do usuário através do token JWT
     * 
     * @return UserResource Dados formatados do usuário
     * @throws AuthException Quando usuário não está autenticado
     */
    public function me(): UserResource
    {
        $user = auth('api')->user();

        if (!$user) {
            throw AuthException::tokenNotProvided();
        }

        return new UserResource($user);
    }
}