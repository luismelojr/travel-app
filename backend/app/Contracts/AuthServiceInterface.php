<?php

namespace App\Contracts;

use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;

interface AuthServiceInterface
{
    /**
     * Registra um novo usuário no sistema
     * 
     * @param array $data Dados de registro do usuário
     * @return AuthResource Dados do usuário e token de autenticação
     * @throws \App\Exceptions\AuthException
     */
    public function register(array $data): AuthResource;

    /**
     * Autentica um usuário no sistema
     * 
     * @param array $credentials Credenciais de login (email, password)
     * @return AuthResource Dados do usuário e token de autenticação
     * @throws \App\Exceptions\AuthException
     */
    public function login(array $credentials): AuthResource;

    /**
     * Renova o token de autenticação
     * 
     * @return AuthResource Novo token e dados do usuário
     * @throws \App\Exceptions\AuthException
     */
    public function refresh(): AuthResource;

    /**
     * Efetua logout invalidando o token atual
     * 
     * @return void
     * @throws \App\Exceptions\AuthException
     */
    public function logout(): void;

    /**
     * Retorna dados do usuário autenticado
     * 
     * @return UserResource Dados do usuário
     * @throws \App\Exceptions\AuthException
     */
    public function me(): UserResource;
}