<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\AuthException;
use Tests\TestCase;

class AuthExceptionTest extends TestCase
{
    /**
     * Test AuthException default constructor
     */
    public function test_auth_exception_default_constructor(): void
    {
        $exception = new AuthException();

        $this->assertEquals('Falha na autenticação', $exception->getMessage());
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('AUTH_ERROR', $exception->getErrorCode());
    }

    /**
     * Test AuthException custom constructor
     */
    public function test_auth_exception_custom_constructor(): void
    {
        $exception = new AuthException('Mensagem customizada', 403, 'CUSTOM_ERROR');

        $this->assertEquals('Mensagem customizada', $exception->getMessage());
        $this->assertEquals(403, $exception->getStatusCode());
        $this->assertEquals('CUSTOM_ERROR', $exception->getErrorCode());
    }

    /**
     * Test invalidCredentials factory method
     */
    public function test_invalid_credentials_factory(): void
    {
        $exception = AuthException::invalidCredentials();

        $this->assertEquals('Credenciais inválidas', $exception->getMessage());
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('INVALID_CREDENTIALS', $exception->getErrorCode());
    }

    /**
     * Test tokenExpired factory method
     */
    public function test_token_expired_factory(): void
    {
        $exception = AuthException::tokenExpired();

        $this->assertEquals('Token expirado', $exception->getMessage());
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('TOKEN_EXPIRED', $exception->getErrorCode());
    }

    /**
     * Test tokenNotProvided factory method
     */
    public function test_token_not_provided_factory(): void
    {
        $exception = AuthException::tokenNotProvided();

        $this->assertEquals('Token não fornecido', $exception->getMessage());
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('TOKEN_NOT_PROVIDED', $exception->getErrorCode());
    }

    /**
     * Test invalidToken factory method
     */
    public function test_invalid_token_factory(): void
    {
        $exception = AuthException::invalidToken();

        $this->assertEquals('Token inválido', $exception->getMessage());
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('INVALID_TOKEN', $exception->getErrorCode());
    }

    /**
     * Test userNotFound factory method
     */
    public function test_user_not_found_factory(): void
    {
        $exception = AuthException::userNotFound();

        $this->assertEquals('Usuário não encontrado', $exception->getMessage());
        $this->assertEquals(404, $exception->getStatusCode());
        $this->assertEquals('USER_NOT_FOUND', $exception->getErrorCode());
    }

    /**
     * Test render method returns JsonResponse
     */
    public function test_render_returns_json_response(): void
    {
        $exception = AuthException::invalidCredentials();
        $response = $exception->render();

        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Credenciais inválidas', $data['message']);
        $this->assertEquals('INVALID_CREDENTIALS', $data['error_code']);
        $this->assertNull($data['data']);
    }
}