<?php

namespace App\Contracts;

use App\Http\Resources\TravelRequestResource;
use App\Models\TravelRequest;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface TravelRequestServiceInterface
{
    /**
     * Cria um novo pedido de viagem
     *
     * @param array $data Dados do pedido de viagem
     * @return TravelRequestResource Dados do pedido criado
     * @throws \App\Exceptions\ApiValidationException
     */
    public function create(array $data): TravelRequestResource;

    /**
     * Busca um pedido de viagem por ID
     *
     * @param int $id ID do pedido de viagem
     * @return TravelRequestResource Dados do pedido encontrado
     * @throws \App\Exceptions\ResourceNotFoundException
     */
    public function findById(int $id): TravelRequestResource;

    /**
     * Lista todos os pedidos de viagem com filtros opcionais
     *
     * @param array $filters Filtros aplicados (status, destination, date_from, date_to, request_date_from, request_date_to)
     * @return AnonymousResourceCollection Lista de pedidos de viagem
     */
    public function list(array $filters = []): AnonymousResourceCollection;

    /**
     * Atualiza o status de um pedido de viagem
     *
     * @param int $id ID do pedido de viagem
     * @param string $status Novo status
     * @return TravelRequestResource Dados do pedido atualizado
     * @throws \App\Exceptions\ResourceNotFoundException
     * @throws \App\Exceptions\ApiValidationException
     */
    public function updateStatus(int $id, string $status): TravelRequestResource;

    /**
     * Cancela um pedido de viagem (apenas pedidos não aprovados)
     *
     * @param int $id ID do pedido de viagem
     * @return TravelRequestResource Dados do pedido cancelado
     * @throws \App\Exceptions\ResourceNotFoundException
     * @throws \App\Exceptions\ApiValidationException
     */
    public function cancel(int $id): TravelRequestResource;
}
