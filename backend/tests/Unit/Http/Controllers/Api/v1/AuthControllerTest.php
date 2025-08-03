<?php

namespace Tests\Unit\Http\Controllers\Api\v1;

use App\Contracts\AuthServiceInterface;
use App\Http\Controllers\Api\v1\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    private AuthServiceInterface $authService;
    private AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->authService = Mockery::mock(AuthServiceInterface::class);
        $this->controller = new AuthController($this->authService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_register_calls_auth_service(): void
    {
        $requestData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        $authResource = Mockery::mock(AuthResource::class);
        
        $request = Mockery::mock(RegisterRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);
        
        $this->authService
            ->shouldReceive('register')
            ->once()
            ->with($requestData)
            ->andReturn($authResource);

        $response = $this->controller->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_register_returns_error_response_on_exception(): void
    {
        $requestData = ['name' => 'John'];

        $request = Mockery::mock(RegisterRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);
        
        $this->authService
            ->shouldReceive('register')
            ->once()
            ->with($requestData)
            ->andThrow(new \Exception('Registration failed'));

        $response = $this->controller->register($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Registration failed', $data['message']);
        $this->assertEquals('REGISTRATION_ERROR', $data['error_code']);
    }

    public function test_login_calls_auth_service(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $authResource = Mockery::mock(AuthResource::class);
        
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($credentials);
        
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with($credentials)
            ->andReturn($authResource);

        $response = $this->controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_login_returns_unauthorized_response_on_exception(): void
    {
        $credentials = [
            'email' => 'john@example.com',
            'password' => 'wrongpassword'
        ];

        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($credentials);
        
        $this->authService
            ->shouldReceive('login')
            ->once()
            ->with($credentials)
            ->andThrow(new \Exception('Invalid credentials'));

        $response = $this->controller->login($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Invalid credentials', $data['message']);
    }

    public function test_refresh_calls_auth_service(): void
    {
        $authResource = Mockery::mock(AuthResource::class);
        
        $this->authService
            ->shouldReceive('refresh')
            ->once()
            ->andReturn($authResource);

        $response = $this->controller->refresh();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_refresh_returns_unauthorized_response_on_exception(): void
    {
        $this->authService
            ->shouldReceive('refresh')
            ->once()
            ->andThrow(new \Exception('Token expired'));

        $response = $this->controller->refresh();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Token expired', $data['message']);
    }

    public function test_logout_calls_auth_service_and_returns_success_response(): void
    {
        $this->authService
            ->shouldReceive('logout')
            ->once()
            ->andReturn();

        $response = $this->controller->logout();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Logout realizado com sucesso', $data['message']);
        $this->assertNull($data['data']);
    }

    public function test_logout_returns_error_response_on_exception(): void
    {
        $this->authService
            ->shouldReceive('logout')
            ->once()
            ->andThrow(new \Exception('Logout failed'));

        $response = $this->controller->logout();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Logout failed', $data['message']);
        $this->assertEquals('LOGOUT_ERROR', $data['error_code']);
    }

    public function test_me_calls_auth_service(): void
    {
        $userResource = Mockery::mock(UserResource::class);
        
        $this->authService
            ->shouldReceive('me')
            ->once()
            ->andReturn($userResource);

        $response = $this->controller->me();

        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_me_returns_unauthorized_response_on_exception(): void
    {
        $this->authService
            ->shouldReceive('me')
            ->once()
            ->andThrow(new \Exception('User not authenticated'));

        $response = $this->controller->me();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('User not authenticated', $data['message']);
    }

    public function test_constructor_sets_auth_service(): void
    {
        $authService = Mockery::mock(AuthServiceInterface::class);
        $controller = new AuthController($authService);

        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('authService');
        $property->setAccessible(true);

        $this->assertSame($authService, $property->getValue($controller));
    }

    public function test_all_methods_handle_different_exception_types(): void
    {
        $runtimeException = new \RuntimeException('Runtime error');
        $invalidArgumentException = new \InvalidArgumentException('Invalid argument');

        $registerRequest = Mockery::mock(RegisterRequest::class);
        $registerRequest->shouldReceive('validated')->andReturn([]);
        
        $loginRequest = Mockery::mock(LoginRequest::class);
        $loginRequest->shouldReceive('validated')->andReturn([]);

        $this->authService->shouldReceive('register')->andThrow($runtimeException);
        $this->authService->shouldReceive('login')->andThrow($invalidArgumentException);
        $this->authService->shouldReceive('refresh')->andThrow($runtimeException);
        $this->authService->shouldReceive('logout')->andThrow($invalidArgumentException);
        $this->authService->shouldReceive('me')->andThrow($runtimeException);

        $registerResponse = $this->controller->register($registerRequest);
        $loginResponse = $this->controller->login($loginRequest);
        $refreshResponse = $this->controller->refresh();
        $logoutResponse = $this->controller->logout();
        $meResponse = $this->controller->me();

        $this->assertEquals(500, $registerResponse->getStatusCode());
        $this->assertEquals(401, $loginResponse->getStatusCode());
        $this->assertEquals(401, $refreshResponse->getStatusCode());
        $this->assertEquals(400, $logoutResponse->getStatusCode());
        $this->assertEquals(401, $meResponse->getStatusCode());
    }

    public function test_response_data_contains_expected_keys(): void
    {
        $this->authService->shouldReceive('logout')->andReturn();

        $response = $this->controller->logout();
        $data = $response->getData(true);

        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_error_responses_contain_error_code(): void
    {
        $this->authService
            ->shouldReceive('logout')
            ->andThrow(new \Exception('Error'));

        $response = $this->controller->logout();
        $data = $response->getData(true);

        $this->assertArrayHasKey('error_code', $data);
        $this->assertEquals('LOGOUT_ERROR', $data['error_code']);
    }

    public function test_unauthorized_responses_contain_unauthorized_error_code(): void
    {
        $this->authService
            ->shouldReceive('me')
            ->andThrow(new \Exception('Not authenticated'));

        $response = $this->controller->me();
        $data = $response->getData(true);

        $this->assertArrayHasKey('error_code', $data);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }
}