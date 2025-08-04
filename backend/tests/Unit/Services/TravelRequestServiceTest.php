<?php

namespace Tests\Unit\Services;

use App\Contracts\TravelRequestServiceInterface;
use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use App\Exceptions\ApiValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Models\User;
use App\Services\TravelRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TravelRequestServiceTest extends TestCase
{
    use RefreshDatabase;

    private TravelRequestServiceInterface $travelRequestService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelRequestService = app(TravelRequestServiceInterface::class);
    }

    public function test_service_implements_interface(): void
    {
        $this->assertInstanceOf(TravelRequestServiceInterface::class, $this->travelRequestService);
    }

    public function test_create_travel_request_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'notes' => 'Viagem de negócios'
        ];

        Log::shouldReceive('info')->once();

        $result = $this->travelRequestService->create($data);

        $this->assertInstanceOf(TravelRequestResource::class, $result);
        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'status' => TravelRequestStatusEnum::REQUESTED->value,
            'notes' => 'Viagem de negócios'
        ]);
    }

    public function test_create_throws_exception_for_past_departure_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::yesterday()->format('Y-m-d'),
            'return_date' => Carbon::today()->format('Y-m-d'),
        ];

        $this->expectException(ApiValidationException::class);
        $this->travelRequestService->create($data);
    }

    public function test_create_throws_exception_for_return_before_departure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->format('Y-m-d'),
        ];

        $this->expectException(ApiValidationException::class);
        $this->travelRequestService->create($data);
    }

    public function test_create_throws_exception_for_same_departure_and_return_date(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => $tomorrow,
            'return_date' => $tomorrow,
        ];

        $this->expectException(ApiValidationException::class);
        $this->travelRequestService->create($data);
    }

    public function test_find_by_id_returns_travel_request(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $result = $this->travelRequestService->findById($travelRequest->id);

        $this->assertInstanceOf(TravelRequestResource::class, $result);
    }

    public function test_find_by_id_throws_exception_for_non_existent_request(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Pedido de viagem não encontrado');

        $this->travelRequestService->findById(999);
    }

    public function test_list_returns_paginated_collection_for_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        TravelRequest::factory()->count(5)->create();

        $result = $this->travelRequestService->list();

        $this->assertInstanceOf(AnonymousResourceCollection::class, $result);
    }

    public function test_list_filters_by_user_for_regular_users(): void
    {
        $user1 = User::factory()->create(['role' => UserRoleEnum::USER]);
        $user2 = User::factory()->create(['role' => UserRoleEnum::USER]);
        
        TravelRequest::factory()->create(['user_id' => $user1->id]);
        TravelRequest::factory()->create(['user_id' => $user2->id]);

        $this->actingAs($user1, 'api');

        $result = $this->travelRequestService->list();
        $collection = $result->collection;

        $this->assertCount(1, $collection);
    }

    public function test_list_filters_by_status(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::CANCELLED]);

        $result = $this->travelRequestService->list(['status' => 'requested']);
        $collection = $result->collection;

        $this->assertCount(1, $collection);
    }

    public function test_list_filters_by_destination(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        TravelRequest::factory()->create(['destination' => 'São Paulo']);
        TravelRequest::factory()->create(['destination' => 'Rio de Janeiro']);
        TravelRequest::factory()->create(['destination' => 'New York']);

        $result = $this->travelRequestService->list(['destination' => 'Paulo']);
        $collection = $result->collection;

        $this->assertCount(1, $collection);
    }

    public function test_list_filters_by_date_range(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        TravelRequest::factory()->create([
            'departure_date' => '2024-01-10',
            'return_date' => '2024-01-15'
        ]);
        TravelRequest::factory()->create([
            'departure_date' => '2024-01-20',
            'return_date' => '2024-01-25'
        ]);

        $filters = [
            'date_from' => '2024-01-18',
            'date_to' => '2024-01-30'
        ];

        $result = $this->travelRequestService->list($filters);
        $collection = $result->collection;

        $this->assertCount(1, $collection);
    }

    public function test_update_status_successfully(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status')
            ->andReturn(true);

        Log::shouldReceive('info')->once();
        Queue::fake();

        $travelRequest = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);

        $result = $this->travelRequestService->updateStatus($travelRequest->id, 'approved');

        $this->assertInstanceOf(TravelRequestResource::class, $result);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => TravelRequestStatusEnum::APPROVED->value
        ]);
    }

    public function test_update_status_throws_exception_for_non_existent_request(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Pedido de viagem não encontrado');

        $this->travelRequestService->updateStatus(999, 'approved');
    }

    public function test_update_status_throws_exception_for_unauthorized_user(): void
    {
        $user = User::factory()->create(['role' => UserRoleEnum::USER]);
        $this->actingAs($user, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status')
            ->andReturn(false);

        $travelRequest = TravelRequest::factory()->create();

        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage('Apenas administradores podem alterar status de pedidos');

        $this->travelRequestService->updateStatus($travelRequest->id, 'approved');
    }

    public function test_update_status_throws_exception_for_invalid_status(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status')
            ->andReturn(true);

        $travelRequest = TravelRequest::factory()->create();

        $this->expectException(ApiValidationException::class);

        $this->travelRequestService->updateStatus($travelRequest->id, 'invalid_status');
    }

    public function test_update_status_validates_status_transition(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status')
            ->andReturn(true);

        $travelRequest = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::CANCELLED]);

        $this->expectException(ApiValidationException::class);

        $this->travelRequestService->updateStatus($travelRequest->id, 'approved');
    }

    public function test_cancel_successfully(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status', \Mockery::any())
            ->andReturn(true);

        Log::shouldReceive('info')->once();
        Queue::fake();

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $result = $this->travelRequestService->cancel($travelRequest->id);

        $this->assertInstanceOf(TravelRequestResource::class, $result);
        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => TravelRequestStatusEnum::CANCELLED->value
        ]);
    }

    public function test_cancel_throws_exception_for_non_existent_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $this->expectException(ResourceNotFoundException::class);
        $this->expectExceptionMessage('Pedido de viagem não encontrado');

        $this->travelRequestService->cancel(999);
    }

    public function test_cancel_throws_exception_for_unauthorized_user(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $this->actingAs($user, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status', \Mockery::any())
            ->andReturn(false);

        $travelRequest = TravelRequest::factory()->create(['user_id' => $anotherUser->id]);

        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage('Você não tem permissão para cancelar este pedido');

        $this->travelRequestService->cancel($travelRequest->id);
    }

    public function test_cancel_throws_exception_for_approved_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status', \Mockery::any())
            ->andReturn(true);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage('Pedidos aprovados não podem ser cancelados');

        $this->travelRequestService->cancel($travelRequest->id);
    }

    public function test_cancel_throws_exception_for_already_cancelled_request(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status', \Mockery::any())
            ->andReturn(true);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        $this->expectException(ApiValidationException::class);
        $this->expectExceptionMessage('Este pedido já está cancelado');

        $this->travelRequestService->cancel($travelRequest->id);
    }

    public function test_validate_travel_dates_with_multiple_errors(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::yesterday()->format('Y-m-d'),
            'return_date' => Carbon::yesterday()->subDays(2)->format('Y-m-d'),
        ];

        try {
            $this->travelRequestService->create($data);
            $this->fail('Expected ApiValidationException was not thrown');
        } catch (ApiValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('departure_date', $errors);
            $this->assertArrayHasKey('return_date', $errors);
        }
    }

    public function test_list_orders_by_created_at_desc(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        $older = TravelRequest::factory()->create(['created_at' => Carbon::now()->subDays(2)]);
        $newer = TravelRequest::factory()->create(['created_at' => Carbon::now()->subDay()]);

        $result = $this->travelRequestService->list();
        $collection = $result->collection;

        $this->assertEquals($newer->id, $collection->first()['id']);
    }

    public function test_list_filters_by_request_date_range(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        $baseDate = Carbon::parse('2024-01-15');
        TravelRequest::factory()->create(['created_at' => $baseDate->copy()->subDays(2)]);
        TravelRequest::factory()->create(['created_at' => $baseDate->copy()->addDays(2)]);

        $filters = [
            'request_date_from' => $baseDate->format('Y-m-d'),
            'request_date_to' => $baseDate->copy()->addDays(5)->format('Y-m-d')
        ];

        $result = $this->travelRequestService->list($filters);
        $collection = $result->collection;

        $this->assertCount(1, $collection);
    }

    public function test_create_sets_default_status_to_requested(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $data = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
        ];

        Log::shouldReceive('info')->once();

        $result = $this->travelRequestService->create($data);
        $resultArray = $result->toArray(request());

        $this->assertEquals('requested', $resultArray['status']['value']);
    }

    public function test_create_handles_optional_notes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        $dataWithoutNotes = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => Carbon::tomorrow()->format('Y-m-d'),
            'return_date' => Carbon::tomorrow()->addDays(5)->format('Y-m-d'),
        ];

        Log::shouldReceive('info')->once();

        $result = $this->travelRequestService->create($dataWithoutNotes);

        $this->assertInstanceOf(TravelRequestResource::class, $result);
        $this->assertDatabaseHas('travel_requests', [
            'user_id' => $user->id,
            'notes' => null
        ]);
    }

    public function test_service_includes_user_relationship_in_responses(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $result = $this->travelRequestService->findById($travelRequest->id);
        $resultArray = $result->toArray(request());

        $this->assertArrayHasKey('user', $resultArray);
        $this->assertEquals($user->id, $resultArray['user']['id']);
    }

    public function test_status_transition_validation_allows_valid_transitions(): void
    {
        $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
        $this->actingAs($admin, 'api');

        Gate::shouldReceive('allows')
            ->with('manage-travel-request-status')
            ->andReturn(true);

        Log::shouldReceive('info')->times(3);
        Queue::fake();

        // REQUESTED -> APPROVED
        $request1 = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        $result1 = $this->travelRequestService->updateStatus($request1->id, 'approved');
        $this->assertInstanceOf(TravelRequestResource::class, $result1);

        // REQUESTED -> CANCELLED
        $request2 = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        $result2 = $this->travelRequestService->updateStatus($request2->id, 'cancelled');
        $this->assertInstanceOf(TravelRequestResource::class, $result2);

        // APPROVED -> CANCELLED
        $request3 = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);
        $result3 = $this->travelRequestService->updateStatus($request3->id, 'cancelled');
        $this->assertInstanceOf(TravelRequestResource::class, $result3);
    }
}