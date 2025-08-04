<?php

namespace Tests\Unit\Jobs;

use App\Jobs\SendTravelRequestNotification;
use App\Mail\TravelRequestStatusNotification;
use App\Models\TravelRequest;
use App\Models\User;
use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Exception;

class SendTravelRequestNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_sends_notification_successfully(): void
    {
        Mail::fake();
        Log::spy();

        $user = User::factory()->create([
            'email' => 'user@test.com',
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);
        $job->handle();

        Mail::assertSent(TravelRequestStatusNotification::class, function ($mail) use ($travelRequest, $previousStatus) {
            return $mail->hasTo('user@test.com') &&
                   $mail->travelRequest->id === $travelRequest->id &&
                   $mail->previousStatus === $previousStatus;
        });

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Notificação de alteração de status enviada com sucesso', [
                'travel_request_id' => $travelRequest->id,
                'user_email' => 'user@test.com',
                'previous_status' => $previousStatus->value,
                'current_status' => $travelRequest->status->value,
            ]);
    }

    public function test_handle_logs_error_and_rethrows_exception_on_failure(): void
    {
        Mail::fake();
        Log::spy();

        Mail::shouldReceive('to')
            ->andThrow(new Exception('Mail service error'));

        $user = User::factory()->create([
            'email' => 'user@test.com',
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mail service error');

        $job->handle();

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Erro ao enviar notificação de alteração de status', \Mockery::on(function ($data) use ($travelRequest) {
                return $data['travel_request_id'] === $travelRequest->id &&
                       $data['user_email'] === 'user@test.com' &&
                       $data['error'] === 'Mail service error' &&
                       isset($data['trace']);
            }));
    }

    public function test_failed_logs_error_when_job_fails(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email' => 'user@test.com',
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;
        $exception = new Exception('Job failed permanently');

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);
        $job->failed($exception);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Job de notificação falhou definitivamente', [
                'travel_request_id' => $travelRequest->id,
                'user_email' => 'user@test.com',
                'error' => 'Job failed permanently',
            ]);
    }

    public function test_constructor_sets_properties_correctly(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);

        $this->assertEquals($travelRequest->id, $job->travelRequest->id);
        $this->assertEquals($previousStatus, $job->previousStatus);
    }

    public function test_handle_with_mail_exception_logs_unknown_email(): void
    {
        Mail::fake();
        Log::spy();

        $user = User::factory()->create([
            'email' => 'user@test.com',
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->make([
            'status' => TravelRequestStatusEnum::APPROVED
        ]);
        $travelRequest->user = null;

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);

        $this->expectException(Exception::class);

        $job->handle();

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Erro ao enviar notificação de alteração de status', \Mockery::on(function ($data) use ($travelRequest) {
                return $data['travel_request_id'] === $travelRequest->id &&
                       $data['user_email'] === 'unknown' &&
                       isset($data['error']) &&
                       isset($data['trace']);
            }));
    }

    public function test_failed_with_null_user_email_logs_unknown(): void
    {
        Log::spy();

        $travelRequest = TravelRequest::factory()->make([
            'status' => TravelRequestStatusEnum::APPROVED
        ]);
        $travelRequest->user = null;

        $previousStatus = TravelRequestStatusEnum::REQUESTED;
        $exception = new Exception('Job failed permanently');

        $job = new SendTravelRequestNotification($travelRequest, $previousStatus);
        $job->failed($exception);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('Job de notificação falhou definitivamente', [
                'travel_request_id' => $travelRequest->id,
                'user_email' => 'unknown',
                'error' => 'Job failed permanently',
            ]);
    }
}