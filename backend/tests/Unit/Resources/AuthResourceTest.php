<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\AuthResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test AuthResource transformation
     */
    public function test_auth_resource_transformation(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Auth',
            'email' => 'luis.auth@example.com',
        ]);

        $authData = [
            'user' => $user,
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $resource = new AuthResource($authData);
        $data = $resource->toArray(request());

        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('token_type', $data);
        $this->assertArrayHasKey('expires_in', $data);

        $this->assertEquals('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...', $data['token']);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertEquals(3600, $data['expires_in']);
    }

    /**
     * Test AuthResource user data is properly nested
     */
    public function test_auth_resource_user_data_nested(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Nested',
            'email' => 'luis.nested@example.com',
        ]);

        $authData = [
            'user' => $user,
            'token' => 'test-token',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $resource = new AuthResource($authData);
        $data = $resource->toArray(request());

        // Verificar se user é uma instância de UserResource
        $this->assertInstanceOf(\App\Http\Resources\UserResource::class, $data['user']);
        
        // Converter UserResource para array para verificar os dados
        $userData = $data['user']->toArray(request());
        $this->assertIsArray($userData);
        $this->assertArrayHasKey('id', $userData);
        $this->assertArrayHasKey('name', $userData);
        $this->assertArrayHasKey('email', $userData);
        $this->assertEquals('Luis Henrique Nested', $userData['name']);
        $this->assertEquals('luis.nested@example.com', $userData['email']);
    }

    /**
     * Test AuthResource with different token types
     */
    public function test_auth_resource_with_different_token_types(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Token Type',
            'email' => 'luis.tokentype@example.com',
        ]);

        $authData = [
            'user' => $user,
            'token' => 'custom-token',
            'token_type' => 'custom',
            'expires_in' => 7200
        ];

        $resource = new AuthResource($authData);
        $data = $resource->toArray(request());

        $this->assertEquals('custom', $data['token_type']);
        $this->assertEquals(7200, $data['expires_in']);
    }

    /**
     * Test AuthResource structure consistency
     */
    public function test_auth_resource_structure_consistency(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Structure',
            'email' => 'luis.structure@example.com',
        ]);

        $authData = [
            'user' => $user,
            'token' => 'structure-test-token',
            'token_type' => 'bearer',
            'expires_in' => 3600
        ];

        $resource = new AuthResource($authData);
        $data = $resource->toArray(request());

        $expectedKeys = ['user', 'token', 'token_type', 'expires_in'];
        
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data);
        }

        // Verificar que não há campos extras
        $this->assertCount(4, $data);
    }
}