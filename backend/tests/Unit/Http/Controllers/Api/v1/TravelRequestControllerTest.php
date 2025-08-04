<?php

namespace Tests\Unit\Http\Controllers\Api\v1;

use App\Contracts\TravelRequestServiceInterface;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Controllers\Api\v1\TravelRequestController;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateStatusRequest;
use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\TestCase;

class TravelRequestControllerTest extends TestCase
{
    private TravelRequestServiceInterface $travelRequestService;
    private TravelRequestController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelRequestService = Mockery::mock(TravelRequestServiceInterface::class);
        $this->controller = new TravelRequestController($this->travelRequestService);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_creates_travel_request_successfully(): void
    {
        $requestData = [
            'requester_name' => 'João Silva',
            'destination' => 'Paris',
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-20',
            'notes' => 'Viagem de negócios'
        ];

        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'requester_name' => 'João Silva',
            'destination' => 'Paris'
        ]);

        $request = Mockery::mock(CreateTravelRequestRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('create')
            ->once()
            ->with($requestData)
            ->andReturn($travelRequestResource);

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function test_store_returns_error_on_exception(): void
    {
        $requestData = ['requester_name' => 'João'];

        $request = Mockery::mock(CreateTravelRequestRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('create')
            ->once()
            ->with($requestData)
            ->andThrow(new \Exception('Validation failed'));

        $response = $this->controller->store($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_CREATION_ERROR', $data['error_code']);
    }

    public function test_show_returns_travel_request_successfully(): void
    {
        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'requester_name' => 'Test User',
            'destination' => 'Test Destination'
        ]);

        $this->travelRequestService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andReturn($travelRequestResource);

        $response = $this->controller->show(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_show_returns_not_found_for_non_existent_request(): void
    {
        $this->travelRequestService
            ->shouldReceive('findById')
            ->once()
            ->with(999)
            ->andThrow(new ResourceNotFoundException('Pedido de viagem não encontrado'));

        $response = $this->controller->show(999);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Pedido de viagem não encontrado', $data['message']);
    }

    public function test_show_returns_forbidden_for_unauthorized_access(): void
    {
        $this->travelRequestService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andThrow(new AuthorizationException('Unauthorized'));

        $response = $this->controller->show(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Você não tem permissão para visualizar este pedido', $data['message']);
    }

    public function test_show_handles_service_exception(): void
    {
        $this->travelRequestService
            ->shouldReceive('findById')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Service error'));

        $response = $this->controller->show(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Service error', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_SHOW_ERROR', $data['error_code']);
    }

    public function test_index_returns_travel_requests_list(): void
    {
        $filters = [
            'status' => 'requested',
            'destination' => 'Paris',
            'date_from' => '2024-01-01',
            'date_to' => '2024-12-31'
        ];

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('only')
            ->once()
            ->with([
                'status',
                'destination',
                'date_from',
                'date_to',
                'request_date_from',
                'request_date_to'
            ])
            ->andReturn($filters);

        $collection = Mockery::mock(AnonymousResourceCollection::class);
        $collection->shouldReceive('jsonSerialize')->andReturn([]);

        $this->travelRequestService
            ->shouldReceive('list')
            ->once()
            ->with($filters)
            ->andReturn($collection);

        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_index_handles_service_exception(): void
    {
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('only')
            ->once()
            ->andReturn([]);

        $this->travelRequestService
            ->shouldReceive('list')
            ->once()
            ->with([])
            ->andThrow(new \Exception('List error'));

        $response = $this->controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('List error', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_LIST_ERROR', $data['error_code']);
    }

    public function test_update_status_successfully(): void
    {
        $requestData = ['status' => 'approved'];
        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'status' => ['value' => 'approved', 'label' => 'Aprovado']
        ]);

        $request = Mockery::mock(UpdateStatusRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('updateStatus')
            ->once()
            ->with(1, 'approved')
            ->andReturn($travelRequestResource);

        $response = $this->controller->updateStatus($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Status do pedido de viagem atualizado com sucesso', $data['message']);
    }

    public function test_update_status_returns_not_found_error(): void
    {
        $requestData = ['status' => 'approved'];

        $request = Mockery::mock(UpdateStatusRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('updateStatus')
            ->once()
            ->with(999, 'approved')
            ->andThrow(new \Exception('Pedido não encontrado'));

        $response = $this->controller->updateStatus($request, 999);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Pedido não encontrado', $data['message']);
    }

    public function test_update_status_returns_forbidden_error(): void
    {
        $requestData = ['status' => 'approved'];

        $request = Mockery::mock(UpdateStatusRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('updateStatus')
            ->once()
            ->with(1, 'approved')
            ->andThrow(new \Exception('Apenas administradores podem alterar status'));

        $response = $this->controller->updateStatus($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Apenas administradores podem alterar status', $data['message']);
    }

    public function test_update_status_returns_generic_error(): void
    {
        $requestData = ['status' => 'approved'];

        $request = Mockery::mock(UpdateStatusRequest::class);
        $request->shouldReceive('validated')->once()->andReturn($requestData);

        $this->travelRequestService
            ->shouldReceive('updateStatus')
            ->once()
            ->with(1, 'approved')
            ->andThrow(new \Exception('Generic error'));

        $response = $this->controller->updateStatus($request, 1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Generic error', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_STATUS_UPDATE_ERROR', $data['error_code']);
    }

    public function test_cancel_successfully(): void
    {
        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'status' => ['value' => 'cancelled', 'label' => 'Cancelado']
        ]);

        $this->travelRequestService
            ->shouldReceive('cancel')
            ->once()
            ->with(1)
            ->andReturn($travelRequestResource);

        $response = $this->controller->cancel(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Pedido de viagem cancelado com sucesso', $data['message']);
    }

    public function test_cancel_returns_not_found_error(): void
    {
        $this->travelRequestService
            ->shouldReceive('cancel')
            ->once()
            ->with(999)
            ->andThrow(new \Exception('Pedido não encontrado'));

        $response = $this->controller->cancel(999);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Pedido não encontrado', $data['message']);
    }

    public function test_cancel_returns_forbidden_error(): void
    {
        $this->travelRequestService
            ->shouldReceive('cancel')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Usuário não tem permissão para cancelar'));

        $response = $this->controller->cancel(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(403, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário não tem permissão para cancelar', $data['message']);
    }

    public function test_cancel_returns_generic_error(): void
    {
        $this->travelRequestService
            ->shouldReceive('cancel')
            ->once()
            ->with(1)
            ->andThrow(new \Exception('Generic cancel error'));

        $response = $this->controller->cancel(1);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Generic cancel error', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_CANCEL_ERROR', $data['error_code']);
    }

    public function test_constructor_sets_travel_request_service(): void
    {
        $service = Mockery::mock(TravelRequestServiceInterface::class);
        $controller = new TravelRequestController($service);

        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('travelRequestService');
        $property->setAccessible(true);

        $this->assertSame($service, $property->getValue($controller));
    }

    public function test_all_methods_return_json_responses(): void
    {
        // Setup mocks for all method calls
        $createRequest = Mockery::mock(CreateTravelRequestRequest::class);
        $createRequest->shouldReceive('validated')->andReturn([]);

        $updateRequest = Mockery::mock(UpdateStatusRequest::class);
        $updateRequest->shouldReceive('validated')->andReturn(['status' => 'approved']);

        $indexRequest = Mockery::mock(Request::class);
        $indexRequest->shouldReceive('only')->andReturn([]);

        // No need to mock TravelRequest::find for this test

        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn(['id' => 1]);

        $this->travelRequestService->shouldReceive('create')->andReturn($travelRequestResource);
        $this->travelRequestService->shouldReceive('findById')->andReturn($travelRequestResource);
        $this->travelRequestService->shouldReceive('list')->andReturn(Mockery::mock(AnonymousResourceCollection::class));
        $this->travelRequestService->shouldReceive('updateStatus')->andReturn($travelRequestResource);
        $this->travelRequestService->shouldReceive('cancel')->andReturn($travelRequestResource);

        $storeResponse = $this->controller->store($createRequest);
        $showResponse = $this->controller->show(999);
        $indexResponse = $this->controller->index($indexRequest);
        $updateStatusResponse = $this->controller->updateStatus($updateRequest, 1);
        $cancelResponse = $this->controller->cancel(1);

        $this->assertInstanceOf(JsonResponse::class, $storeResponse);
        $this->assertInstanceOf(JsonResponse::class, $showResponse);
        $this->assertInstanceOf(JsonResponse::class, $indexResponse);
        $this->assertInstanceOf(JsonResponse::class, $updateStatusResponse);
        $this->assertInstanceOf(JsonResponse::class, $cancelResponse);
    }

    public function test_error_responses_contain_required_keys(): void
    {
        $request = Mockery::mock(CreateTravelRequestRequest::class);
        $request->shouldReceive('validated')->andReturn([]);

        $this->travelRequestService
            ->shouldReceive('create')
            ->andThrow(new \Exception('Test error'));

        $response = $this->controller->store($request);
        $data = $response->getData(true);

        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('error_code', $data);

        $this->assertFalse($data['success']);
        $this->assertEquals('Test error', $data['message']);
        $this->assertEquals('TRAVEL_REQUEST_CREATION_ERROR', $data['error_code']);
    }

    public function test_success_responses_contain_required_keys(): void
    {
        $travelRequestResource = Mockery::mock(TravelRequestResource::class);
        $travelRequestResource->shouldReceive('jsonSerialize')->andReturn([
            'id' => 1,
            'status' => ['value' => 'cancelled', 'label' => 'Cancelado']
        ]);

        $this->travelRequestService
            ->shouldReceive('cancel')
            ->andReturn($travelRequestResource);

        $response = $this->controller->cancel(1);
        $data = $response->getData(true);

        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);

        $this->assertTrue($data['success']);
        $this->assertEquals('Pedido de viagem cancelado com sucesso', $data['message']);
    }
}