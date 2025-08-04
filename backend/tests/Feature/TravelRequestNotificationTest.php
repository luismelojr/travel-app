<?php

namespace Tests\Feature;

use App\Jobs\SendTravelRequestNotification;
use App\Models\User;
use App\Models\TravelRequest;
use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TravelRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_job_is_dispatched_when_request_is_approved(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'role' => UserRoleEnum::USER,
            'email' => 'user@test.com'
        ]);

        $admin = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'email' => 'admin@test.com'
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}/status", [
                'status' => TravelRequestStatusEnum::APPROVED->value
            ]);

        $response->assertStatus(200);

        Queue::assertPushed(SendTravelRequestNotification::class, function ($job) use ($travelRequest) {
            return $job->travelRequest->id === $travelRequest->id &&
                   $job->previousStatus === TravelRequestStatusEnum::REQUESTED;
        });
    }

    public function test_notification_job_is_dispatched_when_request_is_cancelled(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'role' => UserRoleEnum::ADMIN,
            'email' => 'user@test.com'
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->patchJson("/api/v1/travel-requests/{$travelRequest->id}/cancel");

        $response->assertStatus(200);

        Queue::assertPushed(SendTravelRequestNotification::class, function ($job) use ($travelRequest) {
            return $job->travelRequest->id === $travelRequest->id &&
                   $job->previousStatus === TravelRequestStatusEnum::REQUESTED;
        });
    }
}
