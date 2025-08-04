<?php

namespace App\Providers;

use App\Enums\UserRoleEnum;
use App\Models\TravelRequest;
use App\Models\User;
use App\Policies\TravelRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        TravelRequest::class => TravelRequestPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate para administradores
        Gate::define('admin', function (User $user) {
            return $user->role === UserRoleEnum::ADMIN;
        });

        // Gate para gerenciar status de pedidos de viagem
        Gate::define('manage-travel-request-status', function (User $user) {
            return $user->role === UserRoleEnum::ADMIN;
        });

        // Gate para verificar se é dono do pedido
        Gate::define('own-travel-request', function (User $user, TravelRequest $travelRequest) {
            return $user->id === $travelRequest->user_id;
        });
    }
}
