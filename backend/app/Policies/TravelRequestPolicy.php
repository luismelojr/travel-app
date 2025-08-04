<?php

namespace App\Policies;

use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\TravelRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TravelRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Todos os usuários autenticados podem listar pedidos
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TravelRequest $travelRequest): bool
    {
        // Usuários podem ver seus próprios pedidos ou admins podem ver todos
        return $user->id === $travelRequest->user_id || $user->role === UserRoleEnum::ADMIN;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Todos os usuários autenticados podem criar pedidos
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TravelRequest $travelRequest): bool
    {
        // Apenas administradores podem atualizar pedidos de viagem
        return $user->role === UserRoleEnum::ADMIN;
    }

    /**
     * Determine whether the user can update the status of the model.
     */
    public function updateStatus(User $user, TravelRequest $travelRequest): bool
    {
        // Apenas administradores podem alterar status de pedidos
        return $user->role === UserRoleEnum::ADMIN;
    }

    /**
     * Determine whether the user can cancel a travel request.
     * Only non-approved requests can be cancelled.
     */
    public function cancel(User $user, TravelRequest $travelRequest): bool
    {
        // Só pode cancelar pedidos que estão solicitados
        if ($travelRequest->status !== TravelRequestStatusEnum::REQUESTED) {
            return false;
        }
        
        // Usuários podem cancelar seus próprios pedidos OU admins podem cancelar qualquer pedido
        return ($user->id === $travelRequest->user_id) || ($user->role === UserRoleEnum::ADMIN);
    }

    /**
     * Determine whether the user can cancel the model after approval.
     * @deprecated Use cancel() method instead
     */
    public function cancelAfterApproval(User $user, TravelRequest $travelRequest): bool
    {
        // Esta funcionalidade foi removida - não é mais permitido cancelar pedidos aprovados
        return false;
    }

    /**
     * Determine whether the user can cancel their own request.
     * @deprecated Use cancel() method instead
     */
    public function cancelOwn(User $user, TravelRequest $travelRequest): bool
    {
        // Redirecionado para o método cancel() que tem a nova lógica
        return $this->cancel($user, $travelRequest);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TravelRequest $travelRequest): bool
    {
        // Apenas o próprio usuário pode deletar seus pedidos e apenas se estiver "solicitado"
        return $user->id === $travelRequest->user_id 
               && $travelRequest->status === TravelRequestStatusEnum::REQUESTED;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TravelRequest $travelRequest): bool
    {
        // Apenas administradores podem restaurar
        return $user->role === UserRoleEnum::ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TravelRequest $travelRequest): bool
    {
        // Apenas administradores podem deletar permanentemente
        return $user->role === UserRoleEnum::ADMIN;
    }
}