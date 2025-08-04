<?php

namespace App\Services;

use App\Contracts\TravelRequestServiceInterface;
use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use App\Exceptions\ApiValidationException;
use App\Exceptions\ResourceNotFoundException;
use App\Http\Resources\TravelRequestResource;
use App\Jobs\SendTravelRequestNotification;
use App\Models\TravelRequest;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de pedidos de viagem
 *
 * Responsável por toda lógica de negócio relacionada aos pedidos de viagem,
 * incluindo criação, consulta, listagem e atualização de pedidos
 */
class TravelRequestService implements TravelRequestServiceInterface
{
    /**
     * Cria um novo pedido de viagem
     *
     * Valida os dados, verifica se as datas são válidas,
     * cria o pedido e registra log de sucesso
     *
     * @param array $data Dados do pedido de viagem
     * @return TravelRequestResource Dados do pedido criado
     * @throws ApiValidationException Quando dados são inválidos
     */
    public function create(array $data): TravelRequestResource
    {
        $this->validateTravelDates($data['departure_date'], $data['return_date']);

        $travelRequest = TravelRequest::create([
            'user_id' => auth('api')->id(),
            'requester_name' => $data['requester_name'],
            'destination' => $data['destination'],
            'departure_date' => $data['departure_date'],
            'return_date' => $data['return_date'],
            'status' => TravelRequestStatusEnum::REQUESTED,
            'notes' => $data['notes'] ?? null,
        ]);

        Log::info('Pedido de viagem criado com sucesso', [
            'travel_request_id' => $travelRequest->id,
            'user_id' => $travelRequest->user_id,
            'destination' => $travelRequest->destination,
            'departure_date' => $travelRequest->departure_date->format('Y-m-d'),
            'return_date' => $travelRequest->return_date->format('Y-m-d'),
        ]);

        return new TravelRequestResource($travelRequest->load('user'));
    }

    /**
     * Busca um pedido de viagem por ID
     *
     * @param int $id ID do pedido de viagem
     * @return TravelRequestResource Dados do pedido encontrado
     * @throws ResourceNotFoundException Quando pedido não é encontrado
     */
    public function findById(int $id): TravelRequestResource
    {
        $travelRequest = TravelRequest::with('user')->find($id);

        if (!$travelRequest) {
            throw new ResourceNotFoundException('Pedido de viagem não encontrado');
        }

        return new TravelRequestResource($travelRequest);
    }

    /**
     * Lista todos os pedidos de viagem com filtros opcionais
     *
     * Usuários regulares veem apenas seus próprios pedidos.
     * Administradores podem ver todos os pedidos de viagem.
     *
     * @param array $filters Filtros aplicados
     * @return AnonymousResourceCollection Lista de pedidos de viagem
     */
    public function list(array $filters = []): AnonymousResourceCollection
    {
        $user = auth('api')->user();
        $query = TravelRequest::with('user')->orderBy('created_at', 'desc');

        // Filtro por status
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filtro por destino
        if (!empty($filters['destination'])) {
            $query->byDestination($filters['destination']);
        }

        // Filtro por período de viagem
        $dateFrom = !empty($filters['date_from']) ? Carbon::parse($filters['date_from']) : null;
        $dateTo = !empty($filters['date_to']) ? Carbon::parse($filters['date_to']) : null;

        if ($dateFrom || $dateTo) {
            $query->byDateRange($dateFrom, $dateTo);
        }

        // Filtro por período de solicitação
        $requestDateFrom = !empty($filters['request_date_from']) ? Carbon::parse($filters['request_date_from']) : null;
        $requestDateTo = !empty($filters['request_date_to']) ? Carbon::parse($filters['request_date_to']) : null;

        if ($requestDateFrom || $requestDateTo) {
            $query->byRequestDateRange($requestDateFrom, $requestDateTo);
        }

        // Aplicar filtro de usuário baseado no papel:
        // - Usuários regulares: apenas seus próprios pedidos
        // - Administradores: todos os pedidos
        if ($user->role !== UserRoleEnum::ADMIN) {
            $query->where('user_id', $user->id);
        }

        $travelRequests = $query->paginate(15);

        return TravelRequestResource::collection($travelRequests);
    }

    /**
     * Atualiza o status de um pedido de viagem (APENAS ADMINISTRADORES)
     *
     * @param int $id ID do pedido de viagem
     * @param string $status Novo status
     * @return TravelRequestResource Dados do pedido atualizado
     * @throws ResourceNotFoundException Quando pedido não é encontrado
     * @throws ApiValidationException Quando status é inválido ou usuário não autorizado
     */
    public function updateStatus(int $id, string $status): TravelRequestResource
    {
        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            throw new ResourceNotFoundException('Pedido de viagem não encontrado');
        }


        if (!Gate::allows('manage-travel-request-status')) {
            throw new ApiValidationException(['authorization' => ['Apenas administradores podem alterar status de pedidos']],'Apenas administradores podem alterar status de pedidos');
        }


        try {
            $newStatus = TravelRequestStatusEnum::from($status);
        } catch (\ValueError $e) {
            throw new ApiValidationException(['status' => ['Status inválido']]);
        }

        $this->validateStatusTransition($travelRequest->status, $newStatus);


        $oldStatus = $travelRequest->status;
        $travelRequest->update(['status' => $newStatus]);

        Log::info('Status do pedido de viagem atualizado por administrador', [
            'travel_request_id' => $travelRequest->id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'updated_by' => auth('api')->id(),
            'user_role' => auth('api')->user()->role->value,
        ]);

        // Disparar notificação apenas para aprovação ou cancelamento
        if ($newStatus === TravelRequestStatusEnum::APPROVED || $newStatus === TravelRequestStatusEnum::CANCELLED) {
            SendTravelRequestNotification::dispatch($travelRequest, $oldStatus);
        }

        return new TravelRequestResource($travelRequest->load('user'));
    }

    /**
     * Cancela um pedido de viagem (apenas pedidos não aprovados)
     *
     * Usuários podem cancelar seus próprios pedidos não aprovados.
     * Administradores podem cancelar qualquer pedido não aprovado.
     *
     * @param int $id ID do pedido de viagem
     * @return TravelRequestResource Dados do pedido cancelado
     * @throws ResourceNotFoundException Quando pedido não é encontrado
     * @throws ApiValidationException Quando usuário não autorizado ou pedido não pode ser cancelado
     */
    public function cancel(int $id): TravelRequestResource
    {
        $travelRequest = TravelRequest::find($id);

        if (!$travelRequest) {
            throw new ResourceNotFoundException('Pedido de viagem não encontrado');
        }

        // Verificar se o usuário pode cancelar o pedido usando a policy apropriada
        if (!Gate::allows('manage-travel-request-status', $travelRequest)) {
            throw new ApiValidationException(['authorization' => ['Você não tem permissão para cancelar este pedido']], 'Você não tem permissão para cancelar este pedido');
        }

        // Verificar se o pedido pode ser cancelado (não pode estar aprovado)
        if ($travelRequest->status === TravelRequestStatusEnum::APPROVED) {
            throw new ApiValidationException(['status' => ['Pedidos aprovados não podem ser cancelados']], 'Pedidos aprovados não podem ser cancelados');
        }

        // Verificar se já está cancelado
        if ($travelRequest->status === TravelRequestStatusEnum::CANCELLED) {
            throw new ApiValidationException(['status' => ['Este pedido já está cancelado']], 'Este pedido já está cancelado');
        }

        $oldStatus = $travelRequest->status;
        $travelRequest->update(['status' => TravelRequestStatusEnum::CANCELLED]);

        Log::info('Pedido de viagem cancelado', [
            'travel_request_id' => $travelRequest->id,
            'old_status' => $oldStatus->value,
            'new_status' => TravelRequestStatusEnum::CANCELLED->value,
            'cancelled_by' => auth('api')->id(),
            'user_role' => auth('api')->user()->role->value,
            'original_requester' => $travelRequest->user_id,
        ]);

        // Disparar notificação de cancelamento
        SendTravelRequestNotification::dispatch($travelRequest, $oldStatus);

        return new TravelRequestResource($travelRequest->load('user'));
    }

    /**
     * Valida se as datas de viagem são válidas
     *
     * @param string $departureDate Data de partida
     * @param string $returnDate Data de retorno
     * @throws ApiValidationException Quando datas são inválidas
     */
    private function validateTravelDates(string $departureDate, string $returnDate): void
    {
        $departure = Carbon::parse($departureDate);
        $return = Carbon::parse($returnDate);
        $today = Carbon::today();

        $errors = [];

        if ($departure->lt($today)) {
            $errors['departure_date'] = ['A data de partida não pode ser anterior a hoje'];
        }

        if ($return->lt($departure)) {
            $errors['return_date'] = ['A data de retorno não pode ser anterior à data de partida'];
        }

        if ($departure->eq($return)) {
            $errors['return_date'] = ['A data de retorno deve ser diferente da data de partida'];
        }

        if (!empty($errors)) {
            throw new ApiValidationException($errors);
        }
    }

    /**
     * Valida se a transição de status é permitida
     *
     * @param TravelRequestStatusEnum $currentStatus Status atual
     * @param TravelRequestStatusEnum $newStatus Novo status
     * @throws ApiValidationException Quando transição não é permitida
     */
    private function validateStatusTransition(TravelRequestStatusEnum $currentStatus, TravelRequestStatusEnum $newStatus): void
    {
        $allowedTransitions = [
            TravelRequestStatusEnum::REQUESTED->value => [
                TravelRequestStatusEnum::APPROVED->value,
                TravelRequestStatusEnum::CANCELLED->value,
            ],
            TravelRequestStatusEnum::APPROVED->value => [
                TravelRequestStatusEnum::CANCELLED->value,
            ],
            // Cancelled is final state
            TravelRequestStatusEnum::CANCELLED->value => [],
        ];

        if ($currentStatus === TravelRequestStatusEnum::APPROVED && $newStatus !== TravelRequestStatusEnum::CANCELLED) {
            throw new ApiValidationException([
                'status' => [
                    'Pedidos aprovados só podem ser cancelados, não podem ser reprovados ou alterados para outro status'
                ],
            ], 'Transição inválida: Pedido já aprovado');
        }

        if (!in_array($newStatus->value, $allowedTransitions[$currentStatus->value])) {
            throw new ApiValidationException([
                'status' => [
                    sprintf(
                        'Não é possível alterar status de "%s" para "%s"',
                        $currentStatus->label(),
                        $newStatus->label()
                    )
                ],
            ], sprintf(
                'Transição de status inválida: "%s" para "%s"',
                $currentStatus->label(),
                $newStatus->label()
            ));
        }
    }
}
