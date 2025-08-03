<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ApiException;
use Tests\TestCase;

class ApiExceptionTest extends TestCase
{
    /**
     * Test ApiException default constructor
     */
    public function test_api_exception_default_constructor(): void
    {
        $exception = new ApiException('Erro teste');

        $this->assertEquals('Erro teste', $exception->getMessage());
        $this->assertEquals(400, $exception->getStatusCode());
        $this->assertEquals('API_ERROR', $exception->getErrorCode());
        $this->assertNull($exception->getData());
    }

    /**
     * Test ApiException custom constructor with data
     */
    public function test_api_exception_custom_constructor_with_data(): void
    {
        $data = ['field' => 'value'];
        $exception = new ApiException('Mensagem customizada', 422, 'CUSTOM_ERROR', $data);

        $this->assertEquals('Mensagem customizada', $exception->getMessage());
        $this->assertEquals(422, $exception->getStatusCode());
        $this->assertEquals('CUSTOM_ERROR', $exception->getErrorCode());
        $this->assertEquals($data, $exception->getData());
    }

    /**
     * Test badRequest factory method
     */
    public function test_bad_request_factory(): void
    {
        $exception = ApiException::badRequest();

        $this->assertEquals('Requisição inválida', $exception->getMessage());
        $this->assertEquals(400, $exception->getStatusCode());
        $this->assertEquals('BAD_REQUEST', $exception->getErrorCode());
    }

    /**
     * Test forbidden factory method
     */
    public function test_forbidden_factory(): void
    {
        $exception = ApiException::forbidden();

        $this->assertEquals('Acesso negado', $exception->getMessage());
        $this->assertEquals(403, $exception->getStatusCode());
        $this->assertEquals('FORBIDDEN', $exception->getErrorCode());
    }

    /**
     * Test notFound factory method
     */
    public function test_not_found_factory(): void
    {
        $exception = ApiException::notFound();

        $this->assertEquals('Recurso não encontrado', $exception->getMessage());
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('NOT_FOUND', $exception->getErrorCode());
    }

    /**
     * Test conflict factory method
     */
    public function test_conflict_factory(): void
    {
        $exception = ApiException::conflict();

        $this->assertEquals('Conflito de dados', $exception->getMessage());
        $this->assertEquals(409, $exception->getStatusCode());
        $this->assertEquals('CONFLICT', $exception->getErrorCode());
    }

    /**
     * Test unprocessableEntity factory method
     */
    public function test_unprocessable_entity_factory(): void
    {
        $exception = ApiException::unprocessableEntity();

        $this->assertEquals('Entidade não processável', $exception->getMessage());
        $this->assertEquals(422, $exception->getStatusCode());
        $this->assertEquals('UNPROCESSABLE_ENTITY', $exception->getErrorCode());
    }

    /**
     * Test internalServerError factory method
     */
    public function test_internal_server_error_factory(): void
    {
        $exception = ApiException::internalServerError();

        $this->assertEquals('Erro interno do servidor', $exception->getMessage());
        $this->assertEquals(500, $exception->getStatusCode());
        $this->assertEquals('INTERNAL_SERVER_ERROR', $exception->getErrorCode());
    }

    /**
     * Test factory methods with custom message and data
     */
    public function test_factory_methods_with_custom_parameters(): void
    {
        $customData = ['details' => 'Detalhes específicos'];
        $exception = ApiException::conflict('Email já existe', $customData);

        $this->assertEquals('Email já existe', $exception->getMessage());
        $this->assertEquals(409, $exception->getStatusCode());
        $this->assertEquals('CONFLICT', $exception->getErrorCode());
        $this->assertEquals($customData, $exception->getData());
    }

    /**
     * Test render method returns JsonResponse
     */
    public function test_render_returns_json_response(): void
    {
        $data = ['validation' => 'errors'];
        $exception = ApiException::unprocessableEntity('Dados inválidos', $data);
        $response = $exception->render();

        $this->assertEquals(422, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Dados inválidos', $responseData['message']);
        $this->assertEquals('UNPROCESSABLE_ENTITY', $responseData['error_code']);
        $this->assertEquals($data, $responseData['data']);
    }
}