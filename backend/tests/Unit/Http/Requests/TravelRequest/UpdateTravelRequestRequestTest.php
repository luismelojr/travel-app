<?php

namespace Tests\Unit\Http\Requests\TravelRequest;

use App\Http\Requests\TravelRequest\UpdateTravelRequestRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTravelRequestRequestTest extends TestCase
{
    use RefreshDatabase;

    private UpdateTravelRequestRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateTravelRequestRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules(): void
    {
        $expectedRules = [
            'requester_name' => ['sometimes', 'string', 'max:255'],
            'destination' => ['sometimes', 'string', 'max:255'],
            'departure_date' => ['sometimes', 'date', 'after_or_equal:today'],
            'return_date' => ['sometimes', 'date', 'after:departure_date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];

        $this->assertEquals($expectedRules, $this->request->rules());
    }

    public function test_validation_passes_with_empty_data(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_partial_data(): void
    {
        $data = [
            'requester_name' => 'João Silva Updated',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_all_fields(): void
    {
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'notes' => 'Viagem de negócios atualizada'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_past_departure_date(): void
    {
        $data = [
            'departure_date' => Carbon::yesterday()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('departure_date', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_return_date_before_departure(): void
    {
        $data = [
            'departure_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_only_departure_date(): void
    {
        $data = [
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_only_return_date(): void
    {
        // Note: return_date validation depends on departure_date, but when only 
        // return_date is provided, the after:departure_date rule cannot be applied
        $data = [
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_too_long_fields(): void
    {
        $data = [
            'requester_name' => str_repeat('a', 256),
            'destination' => str_repeat('b', 256),
            'notes' => str_repeat('c', 1001),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requester_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    public function test_validation_passes_with_null_notes(): void
    {
        $data = [
            'notes' => null
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_empty_notes(): void
    {
        $data = [
            'notes' => ''
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_non_string_fields(): void
    {
        $data = [
            'requester_name' => 123,
            'destination' => ['array'],
            'notes' => 456
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('requester_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
        $this->assertArrayHasKey('notes', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_invalid_date_format(): void
    {
        $data = [
            'departure_date' => 'invalid-date',
            'return_date' => 'also-invalid',
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('departure_date', $validator->errors()->toArray());
        $this->assertArrayHasKey('return_date', $validator->errors()->toArray());
    }

    public function test_custom_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $expectedMessages = [
            'requester_name.string' => 'O nome do solicitante deve ser um texto.',
            'requester_name.max' => 'O nome do solicitante não pode ter mais de 255 caracteres.',
            
            'destination.string' => 'O destino deve ser um texto.',
            'destination.max' => 'O destino não pode ter mais de 255 caracteres.',
            
            'departure_date.date' => 'A data de partida deve ser uma data válida.',
            'departure_date.after_or_equal' => 'A data de partida não pode ser anterior a hoje.',
            
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
            'requester_name' => 123,
            'destination' => ['invalid'],
        ];

        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors();
        $this->assertEquals('O nome do solicitante deve ser um texto.', $errors->first('requester_name'));
        $this->assertEquals('O destino deve ser um texto.', $errors->first('destination'));
    }

    public function test_request_inherits_from_form_request(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Foundation\Http\FormRequest::class,
            $this->request
        );
    }

    public function test_validation_with_boundary_values(): void
    {
        $data = [
            'requester_name' => str_repeat('a', 255),
            'destination' => str_repeat('b', 255),
            'notes' => str_repeat('c', 1000),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_sometimes_rule_behavior(): void
    {
        // Test that fields are only validated when present
        $data1 = [];
        $validator1 = Validator::make($data1, $this->request->rules());
        $this->assertTrue($validator1->passes());

        $data2 = ['requester_name' => 'Valid Name'];
        $validator2 = Validator::make($data2, $this->request->rules());
        $this->assertTrue($validator2->passes());

        $data3 = ['requester_name' => str_repeat('a', 256)];
        $validator3 = Validator::make($data3, $this->request->rules());
        $this->assertFalse($validator3->passes());
    }

    public function test_date_validation_with_both_dates_provided(): void
    {
        // Valid case: return after departure
        $data1 = [
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
        ];
        $validator1 = Validator::make($data1, $this->request->rules());
        $this->assertTrue($validator1->passes());

        // Invalid case: return before departure
        $data2 = [
            'departure_date' => Carbon::tomorrow()->addDays(3)->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];
        $validator2 = Validator::make($data2, $this->request->rules());
        $this->assertFalse($validator2->passes());
    }

    public function test_departure_date_today_is_valid(): void
    {
        $data = [
            'departure_date' => Carbon::today()->format('Y-m-d'),
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_update_request_differences_from_create_request(): void
    {
        $updateRules = $this->request->rules();
        
        // All rules should use 'sometimes' instead of 'required'
        foreach ($updateRules as $field => $rules) {
            $this->assertContains('sometimes', $rules, "Field {$field} should use 'sometimes' rule");
            $this->assertNotContains('required', $rules, "Field {$field} should not use 'required' rule");
        }

        // Notes should have 'nullable' in addition to 'sometimes'
        $this->assertContains('nullable', $updateRules['notes']);
    }

    public function test_failed_validation_response_structure(): void
    {
        $this->assertTrue(method_exists($this->request, 'failedValidation'));
        
        $reflection = new \ReflectionMethod($this->request, 'failedValidation');
        $this->assertTrue($reflection->isProtected());
    }

    public function test_partial_update_scenarios(): void
    {
        $scenarios = [
            ['requester_name' => 'New Name'],
            ['destination' => 'New Destination'],
            ['departure_date' => Carbon::tomorrow()->format('Y-m-d')],
            ['return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d')],
            ['notes' => 'New notes'],
            ['notes' => null],
            ['notes' => ''],
        ];

        foreach ($scenarios as $data) {
            $validator = Validator::make($data, $this->request->rules());
            $this->assertTrue($validator->passes(), 'Scenario failed: ' . json_encode($data));
        }
    }

    public function test_mixed_valid_and_invalid_fields(): void
    {
        $data = [
            'requester_name' => 'Valid Name',
            'destination' => str_repeat('a', 256), // Invalid - too long
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'notes' => 'Valid notes'
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('destination', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('requester_name', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('departure_date', $validator->errors()->toArray());
        $this->assertArrayNotHasKey('notes', $validator->errors()->toArray());
    }
}