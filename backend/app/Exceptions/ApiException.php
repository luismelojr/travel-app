<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class ApiException extends Exception
{
    protected int $statusCode;
    protected string $errorCode;
    protected mixed $data;

    public function __construct(
        string $message, 
        int $statusCode = 400, 
        string $errorCode = 'API_ERROR', 
        mixed $data = null,
        Exception $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->data = $data;
        parent::__construct($message, 0, $previous);
    }

    public function render(): JsonResponse
    {
        return ResponseHelper::error(
            $this->getMessage(),
            $this->statusCode,
            $this->errorCode,
            $this->data
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

    public function getData(): mixed
    {
        return $this->data;
    }

    public static function badRequest(string $message = 'Requisição inválida', mixed $data = null): self
    {
        return new self($message, 400, 'BAD_REQUEST', $data);
    }

    public static function forbidden(string $message = 'Acesso negado', mixed $data = null): self
    {
        return new self($message, 403, 'FORBIDDEN', $data);
    }

    public static function notFound(string $message = 'Recurso não encontrado', mixed $data = null): self
    {
        return new self($message, 404, 'NOT_FOUND', $data);
    }

    public static function conflict(string $message = 'Conflito de dados', mixed $data = null): self
    {
        return new self($message, 409, 'CONFLICT', $data);
    }

    public static function unprocessableEntity(string $message = 'Entidade não processável', mixed $data = null): self
    {
        return new self($message, 422, 'UNPROCESSABLE_ENTITY', $data);
    }

    public static function internalServerError(string $message = 'Erro interno do servidor', mixed $data = null): self
    {
        return new self($message, 500, 'INTERNAL_SERVER_ERROR', $data);
    }
}