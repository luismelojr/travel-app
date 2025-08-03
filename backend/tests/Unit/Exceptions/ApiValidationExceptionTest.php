<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ApiValidationException;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApiValidationExceptionTest extends TestCase
{
    public function test_constructor_sets_errors_and_default_message(): void
    {
        $errors = ['field1' => ['Error message 1'], 'field2' => ['Error message 2']];
        
        $exception = new ApiValidationException($errors);
        
        $this->assertEquals($errors, $exception->getErrors());
        $this->assertEquals('Dados inválidos fornecidos', $exception->getMessage());
    }

    public function test_constructor_sets_errors_and_custom_message(): void
    {
        $errors = ['email' => ['Email is required']];
        $customMessage = 'Custom validation message';
        
        $exception = new ApiValidationException($errors, $customMessage);
        
        $this->assertEquals($errors, $exception->getErrors());
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function test_constructor_with_empty_errors_array(): void
    {
        $errors = [];
        
        $exception = new ApiValidationException($errors);
        
        $this->assertEquals($errors, $exception->getErrors());
        $this->assertEquals('Dados inválidos fornecidos', $exception->getMessage());
    }

    public function test_get_errors_returns_correct_errors(): void
    {
        $errors = [
            'name' => ['Name is required', 'Name must be string'],
            'email' => ['Email format is invalid']
        ];
        
        $exception = new ApiValidationException($errors);
        
        $this->assertEquals($errors, $exception->getErrors());
    }

    public function test_render_returns_json_response(): void
    {
        $errors = ['field' => ['Field is required']];
        $message = 'Validation failed';
        
        $exception = new ApiValidationException($errors, $message);
        
        $response = $exception->render();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_render_returns_correct_status_code(): void
    {
        $errors = ['field' => ['Field is required']];
        
        $exception = new ApiValidationException($errors);
        
        $response = $exception->render();
        
        $this->assertEquals(422, $response->getStatusCode());
    }

    public function test_render_returns_correct_response_structure(): void
    {
        $errors = ['email' => ['Email is required']];
        $message = 'Custom validation message';
        
        $exception = new ApiValidationException($errors, $message);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('error_code', $data);
        $this->assertArrayHasKey('data', $data);
        
        $this->assertFalse($data['success']);
        $this->assertEquals($message, $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
        $this->assertArrayHasKey('errors', $data['data']);
        $this->assertEquals($errors, $data['data']['errors']);
    }

    public function test_render_with_multiple_field_errors(): void
    {
        $errors = [
            'name' => ['Name is required', 'Name must be at least 2 characters'],
            'email' => ['Email is required'],
            'password' => ['Password must be at least 8 characters', 'Password must contain uppercase']
        ];
        
        $exception = new ApiValidationException($errors);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($errors, $data['data']['errors']);
        $this->assertCount(3, $data['data']['errors']);
    }

    public function test_exception_extends_exception_class(): void
    {
        $exception = new ApiValidationException([]);
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_message_is_inherited_properly(): void
    {
        $customMessage = 'This is a custom error message';
        $exception = new ApiValidationException([], $customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertStringContainsString($customMessage, (string) $exception);
    }

    public function test_errors_property_is_protected(): void
    {
        $reflection = new \ReflectionClass(ApiValidationException::class);
        $property = $reflection->getProperty('errors');
        
        $this->assertTrue($property->isProtected());
        $this->assertFalse($property->isPublic());
        $this->assertFalse($property->isPrivate());
    }

    public function test_render_with_empty_errors(): void
    {
        $exception = new ApiValidationException([]);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals([], $data['data']['errors']);
    }

    public function test_render_with_default_message(): void
    {
        $exception = new ApiValidationException(['field' => ['error']]);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals('Dados inválidos fornecidos', $data['message']);
    }

    public function test_exception_can_be_thrown_and_caught(): void
    {
        $errors = ['test' => ['test error']];
        
        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage('Dados inválidos fornecidos');
        
        throw new ApiValidationException($errors);
    }

    public function test_exception_with_custom_message_can_be_thrown_and_caught(): void
    {
        $errors = ['test' => ['test error']];
        $customMessage = 'Custom exception message';
        
        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage($customMessage);
        
        throw new ApiValidationException($errors, $customMessage);
    }

    public function test_constructor_preserves_original_errors_array(): void
    {
        $originalErrors = [
            'field1' => ['Error 1', 'Error 2'],
            'field2' => ['Error 3']
        ];
        
        $exception = new ApiValidationException($originalErrors);
        
        $this->assertEquals($originalErrors, $exception->getErrors());
        
        // Modify original array to ensure independence
        $originalErrors['field3'] = ['New error'];
        $this->assertNotEquals($originalErrors, $exception->getErrors());
    }

    public function test_render_response_has_correct_content_type(): void
    {
        $exception = new ApiValidationException(['field' => ['error']]);
        
        $response = $exception->render();
        
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function test_errors_with_nested_arrays(): void
    {
        $errors = [
            'user.name' => ['Name is required'],
            'user.email' => ['Email format is invalid'],
            'addresses.0.street' => ['Street is required']
        ];
        
        $exception = new ApiValidationException($errors);
        
        $this->assertEquals($errors, $exception->getErrors());
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($errors, $data['data']['errors']);
    }
}