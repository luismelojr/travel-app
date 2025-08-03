<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class AuthException extends Exception
{
    protected int $statusCode;
    protected string $errorCode;

    public function __construct(
        string $message = "Falha na autenticação", 
        int $statusCode = 401, 
        string $errorCode = 'AUTH_ERROR',
        Exception $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        parent::__construct($message, 0, $previous);
    }

    public function render(): JsonResponse
    {
        return ResponseHelper::error(
            $this->getMessage(),
            $this->statusCode,
            $this->errorCode
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public static function invalidCredentials(): self
    {
        return new self('Credenciais inválidas', 401, 'INVALID_CREDENTIALS');
    }

    public static function tokenExpired(): self
    {
        return new self('Token expirado', 401, 'TOKEN_EXPIRED');
    }

    public static function tokenNotProvided(): self
    {
        return new self('Token não fornecido', 401, 'TOKEN_NOT_PROVIDED');
    }

    public static function invalidToken(): self
    {
        return new self('Token inválido', 401, 'INVALID_TOKEN');
    }

    public static function userNotFound(): self
    {
        return new self('Usuário não encontrado', 404, 'USER_NOT_FOUND');
    }
}