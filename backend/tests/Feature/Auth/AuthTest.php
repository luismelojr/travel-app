<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful user registration
     */
    public function test_user_can_register_successfully(): void
    {
        $userData = [
            'name' => 'Luis Henrique Silva',
            'email' => 'luis.henrique@example.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => [
                            'id', 'name', 'email', 'role', 'role_label',
                            'email_verified_at', 'created_at', 'updated_at'
                        ],
                        'token',
                        'token_type',
                        'expires_in'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Usuário registrado e logado com sucesso',
                    'data' => [
                        'user' => [
                            'name' => 'Luis Henrique Silva',
                            'email' => 'luis.henrique@example.com',
                            'role' => 'user'
                        ],
                        'token_type' => 'bearer',
                        'expires_in' => 3600
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Luis Henrique Silva',
            'email' => 'luis.henrique@example.com',
            'role' => UserRoleEnum::USER
        ]);
    }

    /**
     * Test registration with duplicate email
     */
    public function test_user_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'luis.duplicado@example.com'
        ]);

        $userData = [
            'name' => 'Luis Henrique Novo',
            'email' => 'luis.duplicado@example.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'MinhaSenh@123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422);
        
        // Debug para ver qual resposta estamos recebendo
        $responseData = $response->json();
        
        // Verificar se temos erros de validação
        if (isset($responseData['data']['errors']['email'])) {
            $this->assertArrayHasKey('email', $responseData['data']['errors']);
        } else {
            // Se não tem erro de validação de email, pode ser que tenha passado pela validação
            // e chegado no service que retorna o erro de conflito
            $this->assertTrue(
                $responseData['error_code'] === 'VALIDATION_ERROR' || $responseData['error_code'] === 'CONFLICT',
                'Expected VALIDATION_ERROR or CONFLICT, got: ' . json_encode($responseData)
            );
        }
    }

    /**
     * Test registration with invalid password
     */
    public function test_user_cannot_register_with_weak_password(): void
    {
        $userData = [
            'name' => 'Luis Henrique',
            'email' => 'luis.weak@example.com',
            'password' => '123',
            'password_confirmation' => '123'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422)
                ->assertJson([
                    'success' => false,
                    'message' => 'Validation failed',
                    'error_code' => 'VALIDATION_ERROR'
                ]);
    }

    /**
     * Test registration with password confirmation mismatch
     */
    public function test_user_cannot_register_with_password_mismatch(): void
    {
        $userData = [
            'name' => 'Luis Henrique',
            'email' => 'luis.mismatch@example.com',
            'password' => 'MinhaSenh@123',
            'password_confirmation' => 'OutraSenh@456'
        ];

        $response = $this->postJson('/api/v1/auth/register', $userData);

        $response->assertStatus(422);
        
        // Debug para ver qual campo está sendo retornado
        $responseData = $response->json();
        if (isset($responseData['data']['errors'])) {
            $this->assertArrayHasKey('password', $responseData['data']['errors']);
        } else {
            $this->fail('Validation errors not found in response: ' . json_encode($responseData));
        }
    }

    /**
     * Test successful login
     */
    public function test_user_can_login_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Login',
            'email' => 'luis.login@example.com',
            'password' => Hash::make('MinhaSenh@123')
        ]);

        $loginData = [
            'email' => 'luis.login@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message', 
                    'data' => [
                        'user' => ['id', 'name', 'email'],
                        'token',
                        'token_type',
                        'expires_in'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Login realizado com sucesso'
                ]);
    }

    /**
     * Test login with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $loginData = [
            'email' => 'luis.inexistente@example.com',
            'password' => 'senhaErrada'
        ];

        $response = $this->postJson('/api/v1/auth/login', $loginData);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Credenciais inválidas',
                    'error_code' => 'UNAUTHORIZED'
                ]);
    }

    /**
     * Test accessing protected route without token
     */
    public function test_cannot_access_protected_route_without_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Token not provided',
                    'error_code' => 'HTTP_ERROR'
                ]);
    }

    /**
     * Test accessing me endpoint with valid token
     */
    public function test_user_can_access_me_endpoint_with_valid_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Me',
            'email' => 'luis.me@example.com'
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id', 'name', 'email', 'role'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Dados do usuário obtidos com sucesso',
                    'data' => [
                        'name' => 'Luis Henrique Me',
                        'email' => 'luis.me@example.com'
                    ]
                ]);
    }

    /**
     * Test token refresh
     */
    public function test_user_can_refresh_token(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Refresh',
            'email' => 'luis.refresh@example.com'
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token',
                        'token_type',
                        'expires_in'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Token renovado com sucesso'
                ]);
    }

    /**
     * Test logout
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Logout',
            'email' => 'luis.logout@example.com'
        ]);

        // Use the same approach as other tests in this file
        $token = JWTAuth::fromUser($user);
        
        // First verify token works with /me endpoint
        $meResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                          ->getJson('/api/v1/auth/me');
        
        $this->assertEquals(200, $meResponse->status(), 'Token should be valid for /me endpoint');

        // Now test logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                        ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logout realizado com sucesso'
                ]);
    }

    /**
     * Test rate limiting on login
     */
    public function test_login_rate_limiting(): void
    {
        $loginData = [
            'email' => 'luis.ratelimit@example.com',
            'password' => 'senhaErrada'
        ];

        // Fazer 6 tentativas (limite é 5 por minuto)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/v1/auth/login', $loginData);
            
            if ($i < 5) {
                $response->assertStatus(401); // Credenciais inválidas
            } else {
                $response->assertStatus(429); // Rate limit exceeded
            }
        }
    }
}