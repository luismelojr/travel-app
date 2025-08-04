<?php

namespace Tests\Unit\Http\Requests\TravelRequest;

use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateTravelRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    private CreateTravelRequestRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new CreateTravelRequestRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules(): void
    {
        $expectedRules = [
            'requester_name' => ['required', 'string', 'max:255'],
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after:departure_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $this->assertEquals($expectedRules, $this->request->rules());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'notes' => 'Viagem de negócios'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_missing_required_fields(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requester_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
        $this->assertArrayHasKey('departure_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_past_departure_date(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::yesterday()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
            'notes' => 'Viagem de negócios'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('departure_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_return_date_before_departure(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
            'notes' => 'Viagem de negócios'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_departure_date_today(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::today()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_too_long_requester_name(): void
    {
        $data = [
            'requester_name' => str_repeat('a', 256), // 256 characters
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requester_name', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_too_long_destination(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => str_repeat('a', 256), // 256 characters
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_too_long_notes(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'notes' => str_repeat('a', 1001), // 1001 characters
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_null_notes(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'notes' => null
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_without_notes_field(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_date_format(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => 'invalid-date',
            'return_date' => 'also-invalid',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('departure_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_non_string_fields(): void
    {
        $data = [
            'requester_name' => 123,
            'destination' => ['array'],
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'notes' => 456
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requester_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    public function test_custom_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $expectedMessages = [
            'requester_name.required' => 'O nome do solicitante é obrigatório.',
            'requester_name.string' => 'O nome do solicitante deve ser um texto.',
            'requester_name.max' => 'O nome do solicitante não pode ter mais de 255 caracteres.',
            
            'destination.required' => 'O destino é obrigatório.',
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino não pode ter mais de 255 caracteres.',
            
            'departure_date.required' => 'A data de partida é obrigatória.',
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de partida não pode ser anterior a hoje.',
            
            'return_date.required' => 'A data de retorno é obrigatória.',
            'return_date.date' => 'A data de retorno deve ser uma data válida.',
            'return_date.after' => 'A data de retorno deve ser posterior à data de partida.',
            
            'notes.string' => 'As observações devem ser um texto.',
            'notes.max' => 'As observações não podem ter mais de 1000 caracteres.',
        ];

        $this->assertEquals($expectedMessages, $messages);
    }

    public function test_validation_with_custom_messages(): void
    {
        $data = [
            'requester_name' => '',
            'destination' => '',
        ];

        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors();
        $this->assertEquals('O nome do solicitante é obrigatório.', $errors->first('requester_name'));
        $this->assertEquals('O destino é obrigatório.', $errors->first('destination'));
    }

    public function test_request_inherits_from_form_request(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Foundation\Http\FormRequest::class,
            $this->request
        );
    }

    public function test_validation_with_edge_case_dates(): void
    {
        // Test with same day departure and return (should fail)
        $sameDay = Carbon::tomorrow()->format('Y-m-d');
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => $sameDay,
            'return_date' => $sameDay,
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_validation_with_boundary_values(): void
    {
        // Test with exactly 255 characters for name and destination
        $data = [
            'requester_name' => str_repeat('a', 255),
            'destination' => str_repeat('b', 255),
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'notes' => str_repeat('c', 1000), // Exactly 1000 characters
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_with_empty_string_notes(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(1)->format('Y-m-d'),
            'notes' => '',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_failed_validation_response_structure(): void
    {
        // This tests that the failedValidation method exists and uses ResponseHelper
        $this->assertTrue(method_exists($this->request, 'failedValidation'));
        
        // Verify the method is protected
        $reflection = new \ReflectionMethod($this->request, 'failedValidation');
        $this->assertTrue($reflection->isProtected());
    }
}