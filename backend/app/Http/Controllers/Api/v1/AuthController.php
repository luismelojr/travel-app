<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\AuthServiceInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return ResponseHelper::created($result, 'Usuário registrado e logado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'REGISTRATION_ERROR');
        }
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return ResponseHelper::success($result, 'Login realizado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }

    public function refresh(): JsonResponse
    {
        try {
            $result = $this->authService->refresh();

            return ResponseHelper::success($result, 'Token renovado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout();

            return ResponseHelper::success(null, 'Logout realizado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 400, 'LOGOUT_ERROR');
        }
    }

    public function me(): JsonResponse
    {
        try {
            $result = $this->authService->me();

            return ResponseHelper::success($result, 'Dados do usuário obtidos com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::unauthorized($e->getMessage());
        }
    }
}
