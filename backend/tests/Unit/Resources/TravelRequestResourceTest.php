<?php

namespace Tests\Unit\Resources;

use App\Enums\TravelRequestStatusEnum;
use App\Http\Resources\TravelRequestResource;
use App\Http\Resources\UserResource;
use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_travel_request_resource_transformation(): void
    {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com'
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::parse('2024-06-15'),
            'return_date' => Carbon::parse('2024-06-20'),
            'status' => TravelRequestStatusEnum::REQUESTED,
            'notes' => 'Viagem de negócios',
            'created_at' => Carbon::parse('2024-01-01 10:00:00'),
            'updated_at' => Carbon::parse('2024-01-02 15:30:00'),
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $request = request();
        $data = $resource->toArray($request);

        $expectedData = [
            'id' => $travelRequest->id,
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-20',
            'status' => [
                'value' => 'requested',
                'label' => 'Solicitado',
            ],
            'notes' => 'Viagem de negócios',
            'duration_days' => 6, // 15 to 20 = 6 days (inclusive)
            'user' => (new UserResource($user))->toArray($request),
            'created_at' => '2024-01-01T10:00:00.000000Z',
            'updated_at' => '2024-01-02T15:30:00.000000Z',
        ];

        $this->assertEquals($expectedData, $data);
    }

    public function test_travel_request_resource_with_approved_status(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertEquals('approved', $data['status']['value']);
        $this->assertEquals('Aprovado', $data['status']['label']);
    }

    public function test_travel_request_resource_with_cancelled_status(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertEquals('cancelled', $data['status']['value']);
        $this->assertEquals('Cancelado', $data['status']['label']);
    }

    public function test_travel_request_resource_with_null_notes(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'notes' => null
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertNull($data['notes']);
    }

    public function test_travel_request_resource_contains_all_required_fields(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $requiredFields = [
            'id', 'requester_name', 'destination', 'departure_date',
            'return_date', 'status', 'notes', 'duration_days',
            'user', 'created_at', 'updated_at'
        ];

        foreach ($requiredFields as $field) {
            $this->assertArrayHasKey($field, $data, "Campo '{$field}' está ausente");
        }
    }

    public function test_travel_request_resource_status_structure(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertIsArray($data['status']);
        $this->assertArrayHasKey('value', $data['status']);
        $this->assertArrayHasKey('label', $data['status']);
        
        $this->assertIsString($data['status']['value']);
        $this->assertIsString($data['status']['label']);
    }

    public function test_travel_request_resource_date_formatting(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2024-12-25',
            'return_date' => '2024-12-31'
        ]);

        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertEquals('2024-12-25', $data['departure_date']);
        $this->assertEquals('2024-12-31', $data['return_date']);
        
        // Verify ISO format for timestamps
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            $data['created_at']
        );
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{6}Z$/',
            $data['updated_at']
        );
    }

    public function test_travel_request_resource_duration_days_calculation(): void
    {
        $user = User::factory()->create();
        
        // Test same day trip
        $sameDayTrip = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-15'
        ]);
        $sameDayTrip->load('user');

        $resource = new TravelRequestResource($sameDayTrip);
        $data = $resource->toArray(request());
        $this->assertEquals(1, $data['duration_days']);

        // Test week-long trip
        $weekTrip = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-21'
        ]);
        $weekTrip->load('user');

        $resource = new TravelRequestResource($weekTrip);
        $data = $resource->toArray(request());
        $this->assertEquals(7, $data['duration_days']);
    }

    public function test_travel_request_resource_user_relationship(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com'
        ]);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        // The user relationship returns a UserResource, so we need to convert it to array
        $userData = $data['user'] instanceof UserResource ? $data['user']->toArray(request()) : $data['user'];
        
        $this->assertTrue(is_array($userData) || $data['user'] instanceof UserResource);
        $this->assertEquals($user->id, $userData['id'] ?? $data['user']->resource->id);
        $this->assertEquals('Maria Santos', $userData['name'] ?? $data['user']->resource->name);
        $this->assertEquals('maria@example.com', $userData['email'] ?? $data['user']->resource->email);
    }

    public function test_travel_request_resource_without_loaded_user(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        // Not loading the user relationship

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        // When user is not loaded, the resource should handle this gracefully
        $this->assertArrayHasKey('user', $data);
    }

    public function test_travel_request_resource_data_types(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);
        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertIsInt($data['id']);
        $this->assertIsString($data['requester_name']);
        $this->assertIsString($data['destination']);
        $this->assertIsString($data['departure_date']);
        $this->assertIsString($data['return_date']);
        $this->assertIsArray($data['status']);
        $this->assertIsInt($data['duration_days']);
        $this->assertTrue(is_array($data['user']) || $data['user'] instanceof UserResource);
        $this->assertIsString($data['created_at']);
        $this->assertIsString($data['updated_at']);
        
        // Notes can be string or null
        $this->assertTrue(is_string($data['notes']) || is_null($data['notes']));
    }

    public function test_travel_request_resource_inherits_from_json_resource(): void
    {
        $this->assertInstanceOf(
            \Illuminate\Http\Resources\Json\JsonResource::class,
            new TravelRequestResource(new TravelRequest())
        );
    }

    public function test_travel_request_resource_handles_different_destinations(): void
    {
        $user = User::factory()->create();
        
        $destinations = [
            'São Paulo',
            'New York',
            'Tokyo',
            'London',
            'Berlin'
        ];

        foreach ($destinations as $destination) {
            $travelRequest = TravelRequest::factory()->create([
                'user_id' => $user->id,
                'destination' => $destination
            ]);
            $travelRequest->load('user');

            $resource = new TravelRequestResource($travelRequest);
            $data = $resource->toArray(request());

            $this->assertEquals($destination, $data['destination']);
        }
    }

    public function test_travel_request_resource_handles_all_status_types(): void
    {
        $user = User::factory()->create();
        
        $statusData = [
            ['status' => TravelRequestStatusEnum::REQUESTED, 'value' => 'requested', 'label' => 'Solicitado'],
            ['status' => TravelRequestStatusEnum::APPROVED, 'value' => 'approved', 'label' => 'Aprovado'],
            ['status' => TravelRequestStatusEnum::CANCELLED, 'value' => 'cancelled', 'label' => 'Cancelado'],
        ];

        foreach ($statusData as $statusInfo) {
            $travelRequest = TravelRequest::factory()->create([
                'user_id' => $user->id,
                'status' => $statusInfo['status']
            ]);
            $travelRequest->load('user');

            $resource = new TravelRequestResource($travelRequest);
            $data = $resource->toArray(request());

            $this->assertEquals($statusInfo['value'], $data['status']['value']);
            $this->assertEquals($statusInfo['label'], $data['status']['label']);
        }
    }

    public function test_travel_request_resource_handles_long_notes(): void
    {
        $user = User::factory()->create();
        $longNotes = str_repeat('Este é um texto longo para as observações da viagem. ', 20);
        
        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'notes' => $longNotes
        ]);
        $travelRequest->load('user');

        $resource = new TravelRequestResource($travelRequest);
        $data = $resource->toArray(request());

        $this->assertEquals($longNotes, $data['notes']);
        $this->assertIsString($data['notes']);
    }

    public function test_travel_request_resource_collection(): void
    {
        $user = User::factory()->create();
        $travelRequests = TravelRequest::factory()->count(3)->create(['user_id' => $user->id]);
        
        $collection = TravelRequestResource::collection($travelRequests);
        
        $this->assertInstanceOf(
            \Illuminate\Http\Resources\Json\AnonymousResourceCollection::class,
            $collection
        );
        
        $data = $collection->toArray(request());
        $this->assertCount(3, $data);
        
        foreach ($data as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('requester_name', $item);
            $this->assertArrayHasKey('destination', $item);
        }
    }
}