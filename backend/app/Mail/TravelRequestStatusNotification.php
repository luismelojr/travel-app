<?php

namespace App\Mail;

use App\Models\TravelRequest;
use App\Enums\TravelRequestStatusEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TravelRequestStatusNotification extends Mailable
{
    use Queueable, SerializesModels;

    public TravelRequest $travelRequest;
    public TravelRequestStatusEnum $previousStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(TravelRequest $travelRequest, TravelRequestStatusEnum $previousStatus)
    {
        $this->travelRequest = $travelRequest;
        $this->previousStatus = $previousStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->travelRequest->status) {
            TravelRequestStatusEnum::APPROVED => 'Pedido de Viagem Aprovado',
            TravelRequestStatusEnum::CANCELLED => 'Pedido de Viagem Cancelado',
            default => 'Atualização do Pedido de Viagem'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.travel-request-status',
            with: [
                'travelRequest' => $this->travelRequest,
                'previousStatus' => $this->previousStatus,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
