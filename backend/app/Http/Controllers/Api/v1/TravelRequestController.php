<?php

namespace App\Http\Controllers\Api\v1;

use App\Contracts\TravelRequestServiceInterface;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TravelRequest\CreateTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateTravelRequestRequest;
use App\Http\Requests\TravelRequest\UpdateStatusRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TravelRequestController extends Controller
{
    private TravelRequestServiceInterface $travelRequestService;

    public function __construct(TravelRequestServiceInterface $travelRequestService)
    {
        $this->travelRequestService = $travelRequestService;
    }

    /**
     * Cria um novo pedido de viagem
     */
    public function store(CreateTravelRequestRequest $request): JsonResponse
    {
        try {
            $travelRequest = $this->travelRequestService->create($request->validated());

            return ResponseHelper::created($travelRequest, 'Pedido de viagem criado com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_CREATION_ERROR');
        }
    }

    /**
     * Consulta um pedido de viagem por ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->findById($id);
            return ResponseHelper::success($result, 'Pedido de viagem encontrado');

        } catch (\App\Exceptions\ResourceNotFoundException $e) {
            return ResponseHelper::notFound('Pedido de viagem não encontrado');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return ResponseHelper::forbidden('Você não tem permissão para visualizar este pedido');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_SHOW_ERROR');
        }
    }

    /**
     * Lista todos os pedidos de viagem com filtros opcionais
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status',
                'destination',
                'date_from',
                'date_to',
                'request_date_from',
                'request_date_to'
            ]);

            $travelRequests = $this->travelRequestService->list($filters);

            return ResponseHelper::success($travelRequests, 'Lista de pedidos de viagem obtida com sucesso');
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500, 'TRAVEL_REQUEST_LIST_ERROR');
        }
    }

    /**
     * Atualiza o status de um pedido de viagem (APENAS ADMINISTRADORES)
     */
    public function updateStatus(UpdateStatusRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->updateStatus($id, $request->validated()['status']);
            return ResponseHelper::success($result, 'Status do pedido de viagem atualizado com sucesso');

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'não encontrado')) {
                return ResponseHelper::notFound($e->getMessage());
            }

            if (str_contains($e->getMessage(), 'Apenas administradores')) {
                return ResponseHelper::forbidden($e->getMessage());
            }

            return ResponseHelper::error($e->getMessage(), 400, 'TRAVEL_REQUEST_STATUS_UPDATE_ERROR');
        }
    }

    /**
     * Cancela um pedido de viagem (apenas pedidos não aprovados)
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $result = $this->travelRequestService->cancel($id);
            return ResponseHelper::success($result, 'Pedido de viagem cancelado com sucesso');

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'não encontrado')) {
                return ResponseHelper::notFound($e->getMessage());
            }

            if (str_contains($e->getMessage(), 'não tem permissão')) {
                return ResponseHelper::forbidden($e->getMessage());
            }

            return ResponseHelper::error($e->getMessage(), 400, 'TRAVEL_REQUEST_CANCEL_ERROR');
        }
    }
}
