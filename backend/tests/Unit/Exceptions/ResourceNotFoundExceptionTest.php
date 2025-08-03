<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ResourceNotFoundException;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ResourceNotFoundExceptionTest extends TestCase
{
    public function test_constructor_sets_default_message(): void
    {
        $exception = new ResourceNotFoundException();
        
        $this->assertEquals('Recurso não encontrado', $exception->getMessage());
    }

    public function test_constructor_sets_custom_message(): void
    {
        $customMessage = 'User not found';
        
        $exception = new ResourceNotFoundException($customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
    }

    public function test_constructor_with_empty_string_message(): void
    {
        $exception = new ResourceNotFoundException('');
        
        $this->assertEquals('', $exception->getMessage());
    }

    public function test_render_returns_json_response(): void
    {
        $exception = new ResourceNotFoundException();
        
        $response = $exception->render();
        
        $this->assertInstanceOf(JsonResponse::class, $response);
    }

    public function test_render_returns_correct_status_code(): void
    {
        $exception = new ResourceNotFoundException();
        
        $response = $exception->render();
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_render_returns_correct_response_structure(): void
    {
        $message = 'Product not found';
        $exception = new ResourceNotFoundException($message);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('error_code', $data);
        $this->assertArrayHasKey('data', $data);
        
        $this->assertFalse($data['success']);
        $this->assertEquals($message, $data['message']);
        $this->assertEquals('NOT_FOUND', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_render_with_default_message(): void
    {
        $exception = new ResourceNotFoundException();
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals('Recurso não encontrado', $data['message']);
    }

    public function test_render_with_custom_message(): void
    {
        $customMessage = 'Order #123 not found';
        $exception = new ResourceNotFoundException($customMessage);
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($customMessage, $data['message']);
    }

    public function test_exception_extends_exception_class(): void
    {
        $exception = new ResourceNotFoundException();
        
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function test_exception_message_is_inherited_properly(): void
    {
        $customMessage = 'This is a custom not found message';
        $exception = new ResourceNotFoundException($customMessage);
        
        $this->assertEquals($customMessage, $exception->getMessage());
        $this->assertStringContainsString($customMessage, (string) $exception);
    }

    public function test_exception_can_be_thrown_and_caught(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Recurso não encontrado');
        
        throw new ResourceNotFoundException();
    }

    public function test_exception_with_custom_message_can_be_thrown_and_caught(): void
    {
        $customMessage = 'Custom resource not found';
        
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage($customMessage);
        
        throw new ResourceNotFoundException($customMessage);
    }

    public function test_render_response_has_correct_content_type(): void
    {
        $exception = new ResourceNotFoundException();
        
        $response = $exception->render();
        
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function test_exception_with_long_message(): void
    {
        $longMessage = str_repeat('This is a very long error message. ', 20);
        $exception = new ResourceNotFoundException($longMessage);
        
        $this->assertEquals($longMessage, $exception->getMessage());
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($longMessage, $data['message']);
    }

    public function test_exception_with_special_characters_in_message(): void
    {
        $specialMessage = 'Resource with ID "user@domain.com" não encontrado! #404 <error>';
        $exception = new ResourceNotFoundException($specialMessage);
        
        $this->assertEquals($specialMessage, $exception->getMessage());
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($specialMessage, $data['message']);
    }

    public function test_exception_with_unicode_characters(): void
    {
        $unicodeMessage = 'Usuário não encontrado: 用户未找到';
        $exception = new ResourceNotFoundException($unicodeMessage);
        
        $this->assertEquals($unicodeMessage, $exception->getMessage());
        
        $response = $exception->render();
        $data = $response->getData(true);
        
        $this->assertEquals($unicodeMessage, $data['message']);
    }

    public function test_render_response_structure_consistency(): void
    {
        $exception1 = new ResourceNotFoundException();
        $exception2 = new ResourceNotFoundException('Custom message');
        
        $response1 = $exception1->render();
        $response2 = $exception2->render();
        
        $data1 = $response1->getData(true);
        $data2 = $response2->getData(true);
        
        // Both responses should have the same structure
        $this->assertSame(array_keys($data1), array_keys($data2));
        $this->assertEquals($data1['success'], $data2['success']);
        $this->assertEquals($data1['error_code'], $data2['error_code']);
        $this->assertEquals($data1['data'], $data2['data']);
    }

    public function test_multiple_instances_are_independent(): void
    {
        $exception1 = new ResourceNotFoundException('Message 1');
        $exception2 = new ResourceNotFoundException('Message 2');
        
        $this->assertEquals('Message 1', $exception1->getMessage());
        $this->assertEquals('Message 2', $exception2->getMessage());
        $this->assertNotEquals($exception1->getMessage(), $exception2->getMessage());
    }

    public function test_exception_code_defaults(): void
    {
        $exception = new ResourceNotFoundException();
        
        // Default exception code should be 0
        $this->assertEquals(0, $exception->getCode());
    }

    public function test_exception_file_and_line_are_set(): void
    {
        $exception = new ResourceNotFoundException();
        
        $this->assertIsString($exception->getFile());
        $this->assertIsInt($exception->getLine());
        $this->assertGreaterThan(0, $exception->getLine());
    }
}