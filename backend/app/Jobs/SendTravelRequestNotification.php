<?php

namespace App\Jobs;

use App\Mail\TravelRequestStatusNotification;
use App\Models\TravelRequest;
use App\Enums\TravelRequestStatusEnum;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendTravelRequestNotification implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public TravelRequest $travelRequest;
    public TravelRequestStatusEnum $previousStatus;

    /**
     * Create a new job instance.
     */
    public function __construct(TravelRequest $travelRequest, TravelRequestStatusEnum $previousStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Carrega o usuário relacionado
            $this->travelRequest->load('user');

            // Envia o email de notificação
            Mail::to($this->travelRequest->user->email)
                ->send(new TravelRequestStatusNotification($this->travelRequest, $this->previousStatus));

            Log::info('Notificação de alteração de status enviada com sucesso', [
                'travel_request_id' => $this->travelRequest->id,
                'user_email' => $this->travelRequest->user->email,
                'previous_status' => $this->previousStatus->value,
                'current_status' => $this->travelRequest->status->value,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao enviar notificação de alteração de status', [
                'travel_request_id' => $this->travelRequest->id,
                'user_email' => $this->travelRequest->user->email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw para que o job seja marcado como falho e possa ser reprocessado
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Job de notificação falhou definitivamente', [
            'travel_request_id' => $this->travelRequest->id,
            'user_email' => $this->travelRequest->user->email ?? 'unknown',
            'error' => $exception->getMessage(),
        ]);
    }
}
