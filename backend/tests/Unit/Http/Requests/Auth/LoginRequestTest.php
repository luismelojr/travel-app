<?php

namespace Tests\Unit\Http\Requests\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class LoginRequestTest extends TestCase
{
    private LoginRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new LoginRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules(): void
    {
        $expectedRules = [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];

        $this->assertEquals($expectedRules, $this->request->rules());
    }

    public function test_messages_returns_correct_custom_messages(): void
    {
        $expectedMessages = [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais de 255 caracteres.',
            'password.required' => 'A senha é obrigatória.',
            'password.string' => 'A senha deve ser um texto.',
        ];

        $this->assertEquals($expectedMessages, $this->request->messages());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $validData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $validator = Validator::make($validData, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->fails());
        $this->assertEmpty($validator->errors()->toArray());
    }

    public function test_validation_fails_when_email_is_missing(): void
    {
        $invalidData = [
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('O email é obrigatório.', $validator->errors()->get('email'));
    }

    public function test_validation_fails_when_email_is_invalid_format(): void
    {
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('O email deve ter um formato válido.', $validator->errors()->get('email'));
    }

    public function test_validation_fails_when_email_exceeds_max_length(): void
    {
        $invalidData = [
            'email' => str_repeat('a', 250) . '@example.com', // 261 characters
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('O email não pode ter mais de 255 caracteres.', $validator->errors()->get('email'));
    }

    public function test_validation_fails_when_password_is_missing(): void
    {
        $invalidData = [
            'email' => 'test@example.com',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertContains('A senha é obrigatória.', $validator->errors()->get('password'));
    }

    public function test_validation_fails_when_password_is_not_string(): void
    {
        $invalidData = [
            'email' => 'test@example.com',
            'password' => 123456,
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
        $this->assertContains('A senha deve ser um texto.', $validator->errors()->get('password'));
    }

    public function test_validation_fails_with_multiple_errors(): void
    {
        $invalidData = [
            'email' => 'invalid-email',
            'password' => 123456,
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_minimum_valid_data(): void
    {
        $validData = [
            'email' => 'a@b.co',
            'password' => 'a',
        ];

        $validator = Validator::make($validData, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_validation_passes_with_max_length_email(): void
    {
        $validData = [
            'email' => str_repeat('a', 243) . '@example.com', // exactly 255 characters
            'password' => 'password123',
        ];

        $validator = Validator::make($validData, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_failed_validation_throws_http_response_exception(): void
    {
        $this->expectException(HttpResponseException::class);

        $invalidData = [
            'email' => 'invalid-email',
            'password' => '',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $reflection = new \ReflectionClass($this->request);
        $method = $reflection->getMethod('failedValidation');

        $method->invoke($this->request, $validator);
    }

    public function test_validation_accepts_empty_string_as_valid_email_format(): void
    {
        $invalidData = [
            'email' => '',
            'password' => 'password123',
        ];

        $validator = Validator::make($invalidData, $this->request->rules(), $this->request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertContains('O email é obrigatório.', $validator->errors()->get('email'));
    }

    public function test_password_accepts_special_characters(): void
    {
        $validData = [
            'email' => 'test@example.com',
            'password' => '!@#$%^&*()',
        ];

        $validator = Validator::make($validData, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->fails());
    }

    public function test_email_validation_is_case_insensitive(): void
    {
        $validData = [
            'email' => 'TEST@EXAMPLE.COM',
            'password' => 'password123',
        ];

        $validator = Validator::make($validData, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->fails());
    }
}
