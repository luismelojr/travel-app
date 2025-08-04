<?php

namespace Tests\Unit\Mail;

use App\Mail\TravelRequestStatusNotification;
use App\Models\TravelRequest;
use App\Models\User;
use App\Enums\TravelRequestStatusEnum;
use App\Enums\UserRoleEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Tests\TestCase;

class TravelRequestStatusNotificationTest extends TestCase
{
    use RefreshDatabase;

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

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);

        $this->assertEquals($travelRequest->id, $mail->travelRequest->id);
        $this->assertEquals($previousStatus, $mail->previousStatus);
    }

    public function test_envelope_returns_correct_subject_for_approved_status(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Pedido de Viagem Aprovado', $envelope->subject);
    }

    public function test_envelope_returns_correct_subject_for_cancelled_status(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::CANCELLED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Pedido de Viagem Cancelado', $envelope->subject);
    }

    public function test_envelope_returns_default_subject_for_requested_status(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::REQUESTED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $envelope = $mail->envelope();

        $this->assertInstanceOf(Envelope::class, $envelope);
        $this->assertEquals('Atualização do Pedido de Viagem', $envelope->subject);
    }

    public function test_content_returns_correct_content_definition(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $content = $mail->content();

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('emails.travel-request-status', $content->markdown);
        
        $contentWith = $content->with;
        $this->assertArrayHasKey('travelRequest', $contentWith);
        $this->assertArrayHasKey('previousStatus', $contentWith);
        $this->assertEquals($travelRequest->id, $contentWith['travelRequest']->id);
        $this->assertEquals($previousStatus, $contentWith['previousStatus']);
    }

    public function test_attachments_returns_empty_array(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $attachments = $mail->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    public function test_mail_extends_mailable(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);

        $this->assertInstanceOf(\Illuminate\Mail\Mailable::class, $mail);
    }

    public function test_mail_uses_queueable_trait(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);

        $this->assertContains('Illuminate\Bus\Queueable', class_uses($mail));
        $this->assertContains('Illuminate\Queue\SerializesModels', class_uses($mail));
    }

    public function test_envelope_subject_handles_all_status_cases(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $statusSubjects = [
            [TravelRequestStatusEnum::APPROVED, 'Pedido de Viagem Aprovado'],
            [TravelRequestStatusEnum::CANCELLED, 'Pedido de Viagem Cancelado'],
            [TravelRequestStatusEnum::REQUESTED, 'Atualização do Pedido de Viagem'],
        ];

        foreach ($statusSubjects as [$status, $expectedSubject]) {
            $travelRequest = TravelRequest::factory()->create([
                'user_id' => $user->id,
                'status' => $status
            ]);

            $mail = new TravelRequestStatusNotification($travelRequest, TravelRequestStatusEnum::REQUESTED);
            $envelope = $mail->envelope();

            $this->assertEquals($expectedSubject, $envelope->subject, "Subject mismatch for status: {$status->value}");
        }
    }

    public function test_content_with_is_properly_structured(): void
    {
        $user = User::factory()->create([
            'role' => UserRoleEnum::USER
        ]);

        $travelRequest = TravelRequest::factory()->create([
            'user_id' => $user->id,
            'status' => TravelRequestStatusEnum::APPROVED,
            'destination' => 'Test Destination',
            'notes' => 'Test notes'
        ]);

        $previousStatus = TravelRequestStatusEnum::REQUESTED;

        $mail = new TravelRequestStatusNotification($travelRequest, $previousStatus);
        $content = $mail->content();

        $contentWith = $content->with;
        
        $this->assertArrayHasKey('travelRequest', $contentWith);
        $this->assertArrayHasKey('previousStatus', $contentWith);
        
        $this->assertInstanceOf(TravelRequest::class, $contentWith['travelRequest']);
        $this->assertInstanceOf(TravelRequestStatusEnum::class, $contentWith['previousStatus']);
        
        $this->assertEquals($travelRequest->destination, $contentWith['travelRequest']->destination);
        $this->assertEquals($travelRequest->notes, $contentWith['travelRequest']->notes);
        $this->assertEquals($previousStatus, $contentWith['previousStatus']);
    }
}