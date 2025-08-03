<?php

namespace Tests\Unit\Services;

use App\Contracts\AuthServiceInterface;
use App\Enums\UserRoleEnum;
use App\Exceptions\ApiException;
use App\Exceptions\AuthException;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthServiceInterface $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthServiceInterface::class);
    }

    /**
     * Test successful user registration
     */
    public function test_register_creates_user_successfully(): void
    {
        $userData = [
            'name' => 'Luis Henrique Unit',
            'email' => 'luis.unit@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->register($userData);

        $this->assertInstanceOf(AuthResource::class, $result);
        $this->assertDatabaseHas('users', [
            'name' => 'Luis Henrique Unit',
            'email' => 'luis.unit@example.com',
            'role' => UserRoleEnum::USER
        ]);

        $user = User::where('email', 'luis.unit@example.com')->first();
        $this->assertTrue(Hash::check('MinhaSenh@123', $user->password));
    }

    /**
     * Test registration with duplicate email throws exception
     */
    public function test_register_throws_exception_for_duplicate_email(): void
    {
        User::factory()->create([
            'email' => 'luis.duplicate@example.com'
        ]);

        $userData = [
            'name' => 'Luis Henrique Duplicate',
            'email' => 'luis.duplicate@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Email já cadastrado no sistema');

        $this->authService->register($userData);
    }

    /**
     * Test successful login
     */
    public function test_login_authenticates_user_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Login Unit',
            'email' => 'luis.login.unit@example.com',
            'password' => Hash::make('MinhaSenh@123')
        ]);

        $credentials = [
            'email' => 'luis.login.unit@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->login($credentials);

        $this->assertInstanceOf(AuthResource::class, $result);
    }

    /**
     * Test login with invalid credentials throws exception
     */
    public function test_login_throws_exception_for_invalid_credentials(): void
    {
        $credentials = [
            'email' => 'luis.invalid@example.com',
            'password' => 'senhaErrada'
        ];

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Credenciais inválidas');

        $this->authService->login($credentials);
    }

    /**
     * Test token refresh
     */
    public function test_refresh_renews_token_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Refresh Unit',
            'email' => 'luis.refresh.unit@example.com'
        ]);

        $token = JWTAuth::fromUser($user);
        JWTAuth::setToken($token);
        
        // Mock the auth guard to return our user
        $this->actingAs($user, 'api');

        $result = $this->authService->refresh();

        $this->assertInstanceOf(AuthResource::class, $result);
    }

    /**
     * Test refresh with invalid token throws exception
     */
    public function test_refresh_throws_exception_for_invalid_token(): void
    {
        // Mock JWTAuth to throw TokenExpiredException
        JWTAuth::shouldReceive('refresh')
               ->once()
               ->andThrow(new TokenExpiredException('Token has expired'));

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token expirado');

        $this->authService->refresh();
    }

    /**
     * Test logout invalidates token
     */
    public function test_logout_invalidates_token_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Logout Unit',
            'email' => 'luis.logout.unit@example.com'
        ]);

        // Mock the authenticated user
        $this->actingAs($user, 'api');
        
        // Mock JWTAuth methods
        JWTAuth::shouldReceive('getToken')
               ->once()
               ->andReturn('valid-token');
               
        JWTAuth::shouldReceive('invalidate')
               ->once()
               ->with('valid-token')
               ->andReturn(true);

        // Should not throw exception
        $this->authService->logout();
        $this->assertTrue(true); // Assert that no exception was thrown
    }

    /**
     * Test logout with invalid token throws exception
     */
    public function test_logout_throws_exception_for_invalid_token(): void
    {
        // Test without setting an authenticated user (no actingAs call)
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token não fornecido');

        $this->authService->logout();
    }

    /**
     * Test me returns user data
     */
    public function test_me_returns_user_data_successfully(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Me Unit',
            'email' => 'luis.me.unit@example.com'
        ]);

        $this->actingAs($user, 'api');

        $result = $this->authService->me();

        $this->assertInstanceOf(UserResource::class, $result);
    }

    /**
     * Test me throws exception when user not authenticated
     */
    public function test_me_throws_exception_when_not_authenticated(): void
    {
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token não fornecido');

        $this->authService->me();
    }

    /**
     * Test service implements interface
     */
    public function test_service_implements_interface(): void
    {
        $this->assertInstanceOf(AuthServiceInterface::class, $this->authService);
    }

    /**
     * Test registration logs security events
     */
    public function test_register_logs_duplicate_email_attempt(): void
    {
        User::factory()->create([
            'email' => 'luis.log@example.com'
        ]);

        Log::shouldReceive('warning')
           ->once()
           ->with('Tentativa de registro com email duplicado', \Mockery::type('array'));

        $userData = [
            'name' => 'Luis Henrique Log',
            'email' => 'luis.log@example.com',
            'password' => 'MinhaSenh@123'
        ];

        try {
            $this->authService->register($userData);
        } catch (ApiException $e) {
            // Expected exception
        }
    }

    /**
     * Test login logs security events
     */
    public function test_login_logs_invalid_attempt(): void
    {
        Log::shouldReceive('warning')
           ->once()
           ->with('Tentativa de login inválida', \Mockery::type('array'));

        $credentials = [
            'email' => 'luis.logfail@example.com',
            'password' => 'senhaErrada'
        ];

        try {
            $this->authService->login($credentials);
        } catch (AuthException $e) {
            // Expected exception
        }
    }

    /**
     * Test successful login logs success
     */
    public function test_login_logs_success(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Log Success',
            'email' => 'luis.logsuccess@example.com',
            'password' => Hash::make('MinhaSenh@123')
        ]);

        Log::shouldReceive('info')
           ->once()
           ->with('Login realizado com sucesso', \Mockery::type('array'));

        $credentials = [
            'email' => 'luis.logsuccess@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $this->authService->login($credentials);
    }

    /**
     * Test successful registration logs success
     */
    public function test_register_logs_success(): void
    {
        Log::shouldReceive('info')
           ->once()
           ->with('Usuário registrado com sucesso', \Mockery::type('array'));

        $userData = [
            'name' => 'Luis Henrique Register Success',
            'email' => 'luis.registersuccess@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $this->authService->register($userData);
    }

    /**
     * Test register returns correct AuthResource structure
     */
    public function test_register_returns_correct_auth_resource_structure(): void
    {
        $userData = [
            'name' => 'Luis Henrique Structure',
            'email' => 'luis.structure@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->register($userData);
        $data = $result->toArray(request());

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertIsInt($data['expires_in']);
        $this->assertGreaterThan(0, $data['expires_in']);
    }

    /**
     * Test refresh handles different JWT exceptions
     */
    public function test_refresh_handles_different_jwt_exceptions(): void
    {
        // Mock JWTAuth to throw generic JWTException
        JWTAuth::shouldReceive('refresh')
               ->once()
               ->andThrow(new JWTException('Generic JWT error'));

        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token expirado');

        $this->authService->refresh();
    }

    /**
     * Test refresh returns correct AuthResource structure
     */
    public function test_refresh_returns_correct_auth_resource_structure(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Refresh Structure',
            'email' => 'luis.refreshstructure@example.com'
        ]);

        $this->actingAs($user, 'api');
        
        JWTAuth::shouldReceive('refresh')
               ->once()
               ->andReturn('new-refreshed-token');

        $result = $this->authService->refresh();
        $data = $result->toArray(request());

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertEquals('new-refreshed-token', $data['token']);
    }

    /**
     * Test logout logs warning when JWT invalidation fails but user exists
     */
    public function test_logout_logs_warning_when_jwt_invalidation_fails(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Logout Warning',
            'email' => 'luis.logoutwarning@example.com'
        ]);

        $this->actingAs($user, 'api');
        
        JWTAuth::shouldReceive('getToken')
               ->once()
               ->andReturn('expired-token');
               
        JWTAuth::shouldReceive('invalidate')
               ->once()
               ->with('expired-token')
               ->andThrow(new JWTException('Token already blacklisted'));

        Log::shouldReceive('warning')
           ->once()
           ->with('JWT token invalidation failed during logout', \Mockery::type('array'));

        // Should not throw exception since user is still authenticated
        $this->authService->logout();
        $this->assertTrue(true); // Assert that no exception was thrown
    }

    /**
     * Test logout throws token not provided when no user authenticated
     */
    public function test_logout_throws_token_not_provided_when_no_user(): void
    {
        // Test the first condition in logout - when no user is authenticated
        $this->expectException(AuthException::class);
        $this->expectExceptionMessage('Token não fornecido');

        $this->authService->logout();
    }

    /**
     * Test login AuthResource structure
     */
    public function test_login_returns_correct_auth_resource_structure(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Login Structure',
            'email' => 'luis.loginstructure@example.com',
            'password' => Hash::make('MinhaSenh@123')
        ]);

        $credentials = [
            'email' => 'luis.loginstructure@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->login($credentials);
        $data = $result->toArray(request());

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);
        
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertIsString($data['token']);
        $this->assertIsInt($data['expires_in']);
    }

    /**
     * Test expires_in calculation is correct
     */
    public function test_auth_resource_expires_in_calculation(): void
    {
        $user = User::factory()->create([
            'email' => 'luis.expires@example.com',
            'password' => Hash::make('MinhaSenh@123')
        ]);

        $credentials = [
            'email' => 'luis.expires@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->login($credentials);
        $data = $result->toArray(request());

        $expectedExpiresIn = config('jwt.ttl') * 60;
        $this->assertEquals($expectedExpiresIn, $data['expires_in']);
    }

    /**
     * Test user fresh() method is called in register
     */
    public function test_register_returns_fresh_user_data(): void
    {
        $userData = [
            'name' => 'Luis Henrique Fresh',
            'email' => 'luis.fresh@example.com',
            'password' => 'MinhaSenh@123'
        ];

        $result = $this->authService->register($userData);
        $data = $result->toArray(request());

        // Verify user data is present and correct
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals('Luis Henrique Fresh', $data['user']['name']);
        $this->assertEquals('luis.fresh@example.com', $data['user']['email']);
        $this->assertNotNull($data['user']['role']);
    }
}