<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class ApiValidationException extends Exception
{
    protected array $errors;

    public function __construct(array $errors, string $message = 'Dados inválidos fornecidos')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return ResponseHelper::validationError($this->errors, $this->getMessage());
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}