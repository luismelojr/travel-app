<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ResponseHelperTest extends TestCase
{
    public function test_success_returns_correct_json_response_with_defaults(): void
    {
        $response = ResponseHelper::success();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Success', $data['message']);
        $this->assertNull($data['data']);
    }

    public function test_success_returns_correct_json_response_with_custom_data(): void
    {
        $customData = ['id' => 1, 'name' => 'Test'];
        $customMessage = 'Custom success message';
        $customStatusCode = 201;

        $response = ResponseHelper::success($customData, $customMessage, $customStatusCode);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($customStatusCode, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals($customData, $data['data']);
    }

    public function test_error_returns_correct_json_response_with_defaults(): void
    {
        $response = ResponseHelper::error();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Error', $data['message']);
        $this->assertEquals('ERROR', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_error_returns_correct_json_response_with_custom_data(): void
    {
        $customMessage = 'Custom error message';
        $customStatusCode = 500;
        $customErrorCode = 'CUSTOM_ERROR';
        $customData = ['field' => 'error details'];

        $response = ResponseHelper::error($customMessage, $customStatusCode, $customErrorCode, $customData);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals($customStatusCode, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals($customErrorCode, $data['error_code']);
        $this->assertEquals($customData, $data['data']);
    }

    public function test_validation_error_returns_correct_json_response_with_defaults(): void
    {
        $errors = ['field1' => ['Field is required'], 'field2' => ['Field must be string']];

        $response = ResponseHelper::validationError($errors);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
        $this->assertEquals($errors, $data['data']['errors']);
    }

    public function test_validation_error_returns_correct_json_response_with_custom_message(): void
    {
        $errors = ['email' => ['Email is invalid']];
        $customMessage = 'Custom validation message';

        $response = ResponseHelper::validationError($errors, $customMessage);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(422, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals('VALIDATION_ERROR', $data['error_code']);
        $this->assertEquals($errors, $data['data']['errors']);
    }

    public function test_unauthorized_returns_correct_json_response_with_default(): void
    {
        $response = ResponseHelper::unauthorized();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Unauthorized', $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_unauthorized_returns_correct_json_response_with_custom_message(): void
    {
        $customMessage = 'Access denied';

        $response = ResponseHelper::unauthorized($customMessage);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(401, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals('UNAUTHORIZED', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_forbidden_returns_correct_json_response_with_default(): void
    {
        $response = ResponseHelper::forbidden();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Forbidden', $data['message']);
        $this->assertEquals('FORBIDDEN', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_forbidden_returns_correct_json_response_with_custom_message(): void
    {
        $customMessage = 'Permission denied';

        $response = ResponseHelper::forbidden($customMessage);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals('FORBIDDEN', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_not_found_returns_correct_json_response_with_default(): void
    {
        $response = ResponseHelper::notFound();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Resource not found', $data['message']);
        $this->assertEquals('NOT_FOUND', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_not_found_returns_correct_json_response_with_custom_message(): void
    {
        $customMessage = 'User not found';

        $response = ResponseHelper::notFound($customMessage);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals('NOT_FOUND', $data['error_code']);
        $this->assertNull($data['data']);
    }

    public function test_created_returns_correct_json_response_with_defaults(): void
    {
        $response = ResponseHelper::created();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Resource created successfully', $data['message']);
        $this->assertNull($data['data']);
    }

    public function test_created_returns_correct_json_response_with_custom_data(): void
    {
        $customData = ['id' => 1, 'name' => 'New Resource'];
        $customMessage = 'User created successfully';

        $response = ResponseHelper::created($customData, $customMessage);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        
        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals($customMessage, $data['message']);
        $this->assertEquals($customData, $data['data']);
    }

    public function test_all_methods_return_json_response_instance(): void
    {
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::success());
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::error());
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::validationError([]));
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::unauthorized());
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::forbidden());
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::notFound());
        $this->assertInstanceOf(JsonResponse::class, ResponseHelper::created());
    }

    public function test_response_structure_consistency(): void
    {
        $successResponse = ResponseHelper::success();
        $errorResponse = ResponseHelper::error();
        
        $successData = $successResponse->getData(true);
        $errorData = $errorResponse->getData(true);
        
        $this->assertArrayHasKey('success', $successData);
        $this->assertArrayHasKey('message', $successData);
        $this->assertArrayHasKey('data', $successData);
        
        $this->assertArrayHasKey('success', $errorData);
        $this->assertArrayHasKey('message', $errorData);
        $this->assertArrayHasKey('error_code', $errorData);
        $this->assertArrayHasKey('data', $errorData);
    }
}