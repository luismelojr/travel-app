<?php

namespace Tests\Unit\Http\Requests\TravelRequest;

use App\Http\Requests\TravelRequest\UpdateStatusRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateStatusRequestTest extends TestCase
{
    use RefreshDatabase;

    private UpdateStatusRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        $this->request = new UpdateStatusRequest();
    }

    public function test_authorize_returns_true(): void
    {
        $this->assertTrue($this->request->authorize());
    }

    public function test_rules_returns_correct_validation_rules(): void
    {
        $rules = $this->request->rules();

        $this->assertArrayHasKey('status', $rules);
        $this->assertContains('required', $rules['status']);
        
        // Check that it has Rule::in validation
        $hasInRule = false;
        foreach ($rules['status'] as $rule) {
            if (is_object($rule)) {
                $hasInRule = true;
                break;
            }
        }
        $this->assertTrue($hasInRule, 'Status field should have Rule::in validation');
    }

    public function test_validation_passes_with_valid_status_requested(): void
    {
        $data = ['status' => 'requested'];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_valid_status_approved(): void
    {
        $data = ['status' => 'approved'];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_passes_with_valid_status_cancelled(): void
    {
        $data = ['status' => 'cancelled'];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_with_invalid_status(): void
    {
        $data = ['status' => 'invalid_status'];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_missing_status(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_null_status(): void
    {
        $data = ['status' => null];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_empty_status(): void
    {
        $data = ['status' => ''];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_numeric_status(): void
    {
        $data = ['status' => 1];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_array_status(): void
    {
        $data = ['status' => ['requested']];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_validation_fails_with_boolean_status(): void
    {
        $data = ['status' => true];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertArrayHasKey('status', $validator->errors()->toArray());
    }

    public function test_custom_messages_are_defined(): void
    {
        $messages = $this->request->messages();

        $expectedMessages = [
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser: solicitado, aprovado ou cancelado.',
        ];

        $this->assertEquals($expectedMessages, $messages);
    }

    public function test_validation_with_custom_messages(): void
    {
        $data = ['status' => 'invalid'];

        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors();
        $this->assertEquals('O status deve ser: solicitado, aprovado ou cancelado.', $errors->first('status'));
    }

    public function test_validation_missing_status_custom_message(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules(), $this->request->messages());

        $this->assertFalse($validator->passes());
        
        $errors = $validator->errors();
        $this->assertEquals('O status é obrigatório.', $errors->first('status'));
    }

    public function test_request_inherits_from_form_request(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Foundation\Http\FormRequest::class,
            $this->request
        );
    }

    public function test_all_valid_statuses_are_accepted(): void
    {
        $validStatuses = ['requested', 'approved', 'cancelled'];

        foreach ($validStatuses as $status) {
            $data = ['status' => $status];
            $validator = Validator::make($data, $this->request->rules());
            
            $this->assertTrue(
                $validator->passes(),
                "Status '{$status}' should be valid but validation failed"
            );
        }
    }

    public function test_case_sensitivity_of_status_values(): void
    {
        $invalidCases = [
            'REQUESTED',
            'Requested',
            'APPROVED',
            'Approved',
            'CANCELLED',
            'Cancelled',
            'Canceled', // Common alternative spelling
        ];

        foreach ($invalidCases as $status) {
            $data = ['status' => $status];
            $validator = Validator::make($data, $this->request->rules());
            
            $this->assertFalse(
                $validator->passes(),
                "Status '{$status}' should be invalid (case sensitive) but validation passed"
            );
        }
    }

    public function test_validation_with_whitespace(): void
    {
        $invalidStatuses = [
            ' requested',
            'requested ',
            ' requested ',
            'reque sted',
            "\trequested",
            "requested\n",
        ];

        foreach ($invalidStatuses as $status) {
            $data = ['status' => $status];
            $validator = Validator::make($data, $this->request->rules());
            
            $this->assertFalse(
                $validator->passes(),
                "Status '{$status}' with whitespace should be invalid but validation passed"
            );
        }
    }

    public function test_validation_with_extra_fields(): void
    {
        $data = [
            'status' => 'approved',
            'extra_field' => 'should be ignored',
            'another_field' => 123
        ];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertTrue($validator->passes());
        // Extra fields should not cause validation to fail
    }

    public function test_failed_validation_response_structure(): void
    {
        $this->assertTrue(method_exists($this->request, 'failedValidation'));
        
        $reflection = new \ReflectionMethod($this->request, 'failedValidation');
        $this->assertTrue($reflection->isProtected());
    }

    public function test_request_only_validates_status_field(): void
    {
        $rules = $this->request->rules();
        
        $this->assertCount(1, $rules, 'Request should only validate the status field');
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_status_validation_rule_order(): void
    {
        $rules = $this->request->rules();
        $statusRules = $rules['status'];

        // The first rule should be 'required'
        $this->assertEquals('required', $statusRules[0]);
        
        // The second rule should be the Rule::in validation
        $this->assertTrue(is_object($statusRules[1]), 'Second rule should be a Rule object');
    }

    public function test_validation_error_count_with_invalid_status(): void
    {
        $data = ['status' => 'invalid_status'];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertCount(1, $validator->errors()->get('status'));
    }

    public function test_validation_error_count_with_missing_status(): void
    {
        $data = [];

        $validator = Validator::make($data, $this->request->rules());

        $this->assertFalse($validator->passes());
        $this->assertCount(1, $validator->errors()->get('status'));
    }

    public function test_validation_with_special_characters(): void
    {
        $invalidStatuses = [
            'requested!',
            'approv@d',
            'cancel#ed',
            'status-requested',
            'status_approved',
            'requested.status',
        ];

        foreach ($invalidStatuses as $status) {
            $data = ['status' => $status];
            $validator = Validator::make($data, $this->request->rules());
            
            $this->assertFalse(
                $validator->passes(),
                "Status '{$status}' with special characters should be invalid but validation passed"
            );
        }
    }

    public function test_request_uses_rule_class(): void
    {
        $rules = $this->request->rules();
        $statusRules = $rules['status'];
        
        // Check that Rule::in is being used (imported at the top of the class)
        $ruleClass = null;
        foreach ($statusRules as $rule) {
            if (is_object($rule)) {
                $ruleClass = get_class($rule);
                break;
            }
        }
        
        $this->assertNotNull($ruleClass, 'Should use a Rule class for in validation');
        $this->assertStringContainsString('Rule', $ruleClass);
    }
}