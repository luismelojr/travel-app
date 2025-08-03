<?php

namespace Tests\Unit\Resources;

use App\Enums\UserRoleEnum;
use App\Http\Resources\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test UserResource transformation
     */
    public function test_user_resource_transformation(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Resource',
            'email' => 'luis.resource@example.com',
            'role' => UserRoleEnum::USER,
            'email_verified_at' => Carbon::parse('2024-01-15 10:30:00'),
            'created_at' => Carbon::parse('2024-01-01 09:00:00'),
            'updated_at' => Carbon::parse('2024-01-15 10:30:00'),
        ]);

        $resource = new UserResource($user);
        $request = request();
        $data = $resource->toArray($request);

        $expectedData = [
            'id' => $user->id,
            'name' => 'Luis Henrique Resource',
            'email' => 'luis.resource@example.com',
            'role' => 'user',
            'role_label' => 'Usuário',
            'email_verified_at' => '2024-01-15 10:30:00',
            'created_at' => '2024-01-01 09:00:00',
            'updated_at' => '2024-01-15 10:30:00',
        ];

        $this->assertEquals($expectedData, $data);
    }

    /**
     * Test UserResource with admin role
     */
    public function test_user_resource_with_admin_role(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Admin',
            'email' => 'luis.admin@example.com',
            'role' => UserRoleEnum::ADMIN,
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        $this->assertEquals('admin', $data['role']);
        $this->assertEquals('Administrador', $data['role_label']);
    }

    /**
     * Test UserResource with null email_verified_at
     */
    public function test_user_resource_with_null_email_verified(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Unverified',
            'email' => 'luis.unverified@example.com',
            'email_verified_at' => null,
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        $this->assertNull($data['email_verified_at']);
        $this->assertEquals('Luis Henrique Unverified', $data['name']);
        $this->assertEquals('luis.unverified@example.com', $data['email']);
    }

    /**
     * Test UserResource contains all required fields
     */
    public function test_user_resource_contains_all_required_fields(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Complete',
            'email' => 'luis.complete@example.com',
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        $requiredFields = [
            'id', 'name', 'email', 'role', 'role_label',
            'email_verified_at', 'created_at', 'updated_at'
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $data, "Campo '{$field}' está ausente");
        }
        
        // Verificar se todos os valores esperados estão presentes
        $this->assertEquals('Luis Henrique Complete', $data['name']);
        $this->assertEquals('luis.complete@example.com', $data['email']);
        $this->assertEquals('user', $data['role']);
        $this->assertEquals('Usuário', $data['role_label']);
    }

    /**
     * Test UserResource does not expose sensitive data
     */
    public function test_user_resource_does_not_expose_sensitive_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Luis Henrique Security',
            'email' => 'luis.security@example.com',
        ]);

        $resource = new UserResource($user);
        $data = $resource->toArray(request());

        $sensitiveFields = ['password', 'remember_token'];

        foreach ($sensitiveFields as $field) {
            $this->assertArrayNotHasKey($field, $data, "Campo sensível '{$field}' está sendo exposto");
        }
        
        // Verificar se apenas os campos seguros estão presentes
        $safeFields = ['id', 'name', 'email', 'role', 'role_label', 'email_verified_at', 'created_at', 'updated_at'];
        $this->assertEquals(count($safeFields), count($data), 'Número de campos retornados não confere com o esperado');
    }
}