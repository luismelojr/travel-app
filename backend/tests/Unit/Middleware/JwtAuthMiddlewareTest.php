<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\JwtAuthMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private JwtAuthMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new JwtAuthMiddleware();
    }

    /**
     * Test middleware passes with valid token
     */
    public function test_middleware_passes_with_valid_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Middleware',
            'email' => 'luis.middleware@example.com',
        ]);

        $token = JWTAuth::fromUser($user);
        
        // Mock JWTAuth to return the user
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andReturnSelf();
               
        JWTAuth::shouldReceive('authenticate')
               ->once()
               ->andReturn($user);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer ' . $token);

        $nextCalled = false;
        $next = function ($req) use (&$nextCalled) {
            $nextCalled = true;
            return response('OK');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertTrue($nextCalled);
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test middleware returns error when token is expired
     */
    public function test_middleware_returns_error_when_token_expired(): void
    {
        // Mock JWTAuth to throw TokenExpiredException
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andThrow(new TokenExpiredException('Token has expired'));

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer expired-token');

        $next = function ($req) {
            $this->fail('Next should not be called when token is expired');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Token expirado', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /**
     * Test middleware returns error when token is invalid
     */
    public function test_middleware_returns_error_when_token_invalid(): void
    {
        // Mock JWTAuth to throw TokenInvalidException
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andThrow(new TokenInvalidException('Token is invalid'));

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer invalid-token');

        $next = function ($req) {
            $this->fail('Next should not be called when token is invalid');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Token inválido', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /**
     * Test middleware returns error when token is not provided
     */
    public function test_middleware_returns_error_when_token_not_provided(): void
    {
        // Mock JWTAuth to throw JWTException
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andThrow(new JWTException('Token not provided'));

        $request = Request::create('/test', 'GET');

        $next = function ($req) {
            $this->fail('Next should not be called when token is not provided');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Token não fornecido', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /**
     * Test middleware returns error when user not found
     */
    public function test_middleware_returns_error_when_user_not_found(): void
    {
        // Mock JWTAuth to return null user
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andReturnSelf();
               
        JWTAuth::shouldReceive('authenticate')
               ->once()
               ->andReturn(null);

        $request = Request::create('/test', 'GET');
        $request->headers->set('Authorization', 'Bearer valid-but-no-user');

        $next = function ($req) {
            $this->fail('Next should not be called when user is not found');
        };

        $response = $this->middleware->handle($request, $next);

        $this->assertEquals(401, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário não encontrado', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
    }

    /**
     * Test middleware response format consistency
     */
    public function test_middleware_response_format_consistency(): void
    {
        JWTAuth::shouldReceive('parseToken')
               ->once()
               ->andThrow(new TokenExpiredException('Token expired'));

        $request = Request::create('/test', 'GET');
        $next = function () {};

        $response = $this->middleware->handle($request, $next);
        $data = json_decode($response->getContent(), true);

        // Verificar estrutura padrão da resposta
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('error_code', $data);
        $this->assertArrayHasKey('data', $data);
        
        $this->assertIsBool($data['success']);
        $this->assertIsString($data['message']);
        $this->assertIsString($data['error_code']);
        $this->assertNull($data['data']);
    }
}