<?php

namespace App\Exceptions;

use App\Helpers\ResponseHelper;
use Exception;
use Illuminate\Http\JsonResponse;

class ResourceNotFoundException extends Exception
{
    public function __construct(string $message = 'Recurso não encontrado')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return ResponseHelper::notFound($this->getMessage());
    }
}