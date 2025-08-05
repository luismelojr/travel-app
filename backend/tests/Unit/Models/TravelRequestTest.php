<?php

namespace Tests\Unit\Models;

use App\Enums\TravelRequestStatusEnum;
use App\Models\TravelRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes(): void
    {
        $fillable = [
            'user_id',
            'requester_name',
            'destination',
            'departure_date',
            'return_date',
            'status',
            'notes',
        ];

        $travelRequest = new TravelRequest();
        $this->assertEquals($fillable, $travelRequest->getFillable());
    }

    public function test_casts_attributes(): void
    {
        $travelRequest = new TravelRequest();
        $casts = $travelRequest->getCasts();

        $this->assertEquals('date', $casts['departure_date']);
        $this->assertEquals('date', $casts['return_date']);
        $this->assertEquals(TravelRequestStatusEnum::class, $casts['status']);
    }

    public function test_belongs_to_user_relationship(): void
    {
        $user = User::factory()->create();
        $travelRequest = TravelRequest::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $travelRequest->user);
        $this->assertEquals($user->id, $travelRequest->user->id);
    }

    public function test_scope_by_status_with_enum(): void
    {
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::CANCELLED]);

        $requestedRequests = TravelRequest::byStatus(TravelRequestStatusEnum::REQUESTED)->get();
        $approvedRequests = TravelRequest::byStatus(TravelRequestStatusEnum::APPROVED)->get();

        $this->assertCount(1, $requestedRequests);
        $this->assertCount(1, $approvedRequests);
        $this->assertEquals(TravelRequestStatusEnum::REQUESTED, $requestedRequests->first()->status);
        $this->assertEquals(TravelRequestStatusEnum::APPROVED, $approvedRequests->first()->status);
    }

    public function test_scope_by_status_with_string(): void
    {
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);

        $requestedRequests = TravelRequest::byStatus('requested')->get();
        $approvedRequests = TravelRequest::byStatus('approved')->get();

        $this->assertCount(1, $requestedRequests);
        $this->assertCount(1, $approvedRequests);
    }

    public function test_scope_by_destination(): void
    {
        TravelRequest::factory()->create(['destination' => 'São Paulo']);
        TravelRequest::factory()->create(['destination' => 'Rio de Janeiro']);
        TravelRequest::factory()->create(['destination' => 'New York']);

        $pauloRequests = TravelRequest::byDestination('Paulo')->get();
        $rioRequests = TravelRequest::byDestination('Rio')->get();
        $nyRequests = TravelRequest::byDestination('New')->get();

        $this->assertCount(1, $pauloRequests);
        $this->assertCount(1, $rioRequests);
        $this->assertCount(1, $nyRequests);
        $this->assertEquals('São Paulo', $pauloRequests->first()->destination);
    }

    public function test_scope_by_date_range_with_both_dates(): void
    {
        $startDate = Carbon::parse('2024-01-15');
        $endDate = Carbon::parse('2024-01-25');

        TravelRequest::factory()->create([
            'departure_date' => '2024-01-10',
            'return_date' => '2024-01-20'
        ]);
        TravelRequest::factory()->create([
            'departure_date' => '2024-01-20',
            'return_date' => '2024-01-25'
        ]);
        TravelRequest::factory()->create([
            'departure_date' => '2024-02-01',
            'return_date' => '2024-02-10'
        ]);

        $filteredRequests = TravelRequest::byDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $filteredRequests);
    }

    public function test_scope_by_date_range_with_start_date_only(): void
    {
        $startDate = Carbon::parse('2024-01-15');

        TravelRequest::factory()->create(['departure_date' => '2024-01-10']);
        TravelRequest::factory()->create(['departure_date' => '2024-01-20']);
        TravelRequest::factory()->create(['departure_date' => '2024-02-01']);

        $filteredRequests = TravelRequest::byDateRange($startDate)->get();

        $this->assertCount(2, $filteredRequests);
    }

    public function test_scope_by_date_range_with_end_date_only(): void
    {
        $endDate = Carbon::parse('2024-01-25');

        TravelRequest::factory()->create(['departure_date' => '2024-01-20']);
        TravelRequest::factory()->create(['departure_date' => '2024-01-30']);
        TravelRequest::factory()->create(['departure_date' => '2024-02-10']);

        $filteredRequests = TravelRequest::byDateRange(null, $endDate)->get();

        $this->assertCount(1, $filteredRequests);
    }

    public function test_scope_by_request_date_range(): void
    {
        $baseDate = Carbon::parse('2024-01-15');
        $startDate = $baseDate->copy();
        $endDate = $baseDate->copy()->addDays(5);

        TravelRequest::factory()->create(['created_at' => $baseDate->copy()->subDays(2)]);
        TravelRequest::factory()->create(['created_at' => $baseDate->copy()->addDays(2)]);
        TravelRequest::factory()->create(['created_at' => $baseDate->copy()->addDays(10)]);

        $filteredRequests = TravelRequest::byRequestDateRange($startDate, $endDate)->get();

        $this->assertCount(1, $filteredRequests);
    }

    public function test_get_duration_in_days(): void
    {
        $departureDate = Carbon::parse('2024-01-15');
        $returnDate = Carbon::parse('2024-01-20');

        $travelRequest = TravelRequest::factory()->create([
            'departure_date' => $departureDate,
            'return_date' => $returnDate
        ]);

        $duration = $travelRequest->getDurationInDays();

        $this->assertEquals(6, $duration); // 5 days + 1 (inclusive)
    }

    public function test_get_duration_in_days_same_day(): void
    {
        $date = Carbon::parse('2024-01-15');

        $travelRequest = TravelRequest::factory()->create([
            'departure_date' => $date,
            'return_date' => $date
        ]);

        $duration = $travelRequest->getDurationInDays();

        $this->assertEquals(1, $duration);
    }

    public function test_can_be_approved_delegates_to_enum(): void
    {
        $requestedTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        $approvedTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);
        $cancelledTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::CANCELLED]);

        $this->assertTrue($requestedTravel->canBeApproved());
        $this->assertFalse($approvedTravel->canBeApproved());
        $this->assertFalse($cancelledTravel->canBeApproved());
    }

    public function test_can_be_cancelled_delegates_to_enum(): void
    {
        $requestedTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);
        $approvedTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::APPROVED]);
        $cancelledTravel = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::CANCELLED]);

        $this->assertTrue($requestedTravel->canBeCancelled());
        $this->assertTrue($approvedTravel->canBeCancelled());
        $this->assertFalse($cancelledTravel->canBeCancelled());
    }

    public function test_has_factory(): void
    {
        $this->assertNotNull(TravelRequest::factory());
    }

    public function test_uses_has_factory_trait(): void
    {
        $traits = class_uses(TravelRequest::class);
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', $traits);
    }

    public function test_scope_methods_return_builder(): void
    {
        $travelRequest = new TravelRequest();
        $query = $travelRequest->newQuery();

        $this->assertInstanceOf(Builder::class, $travelRequest->scopeByStatus($query, 'requested'));
        $this->assertInstanceOf(Builder::class, $travelRequest->scopeByDestination($query, 'test'));
        $this->assertInstanceOf(Builder::class, $travelRequest->scopeByDateRange($query));
        $this->assertInstanceOf(Builder::class, $travelRequest->scopeByRequestDateRange($query));
    }

    public function test_model_dates_are_carbon_instances(): void
    {
        $travelRequest = TravelRequest::factory()->create([
            'departure_date' => '2024-01-15',
            'return_date' => '2024-01-20'
        ]);

        $this->assertInstanceOf(Carbon::class, $travelRequest->departure_date);
        $this->assertInstanceOf(Carbon::class, $travelRequest->return_date);
        $this->assertInstanceOf(Carbon::class, $travelRequest->created_at);
        $this->assertInstanceOf(Carbon::class, $travelRequest->updated_at);
    }

    public function test_status_is_cast_to_enum(): void
    {
        $travelRequest = TravelRequest::factory()->create(['status' => TravelRequestStatusEnum::REQUESTED]);

        $this->assertInstanceOf(TravelRequestStatusEnum::class, $travelRequest->status);
        $this->assertEquals(TravelRequestStatusEnum::REQUESTED, $travelRequest->status);
    }

    public function test_model_table_name(): void
    {
        $travelRequest = new TravelRequest();
        $this->assertEquals('travel_requests', $travelRequest->getTable());
    }

    public function test_primary_key(): void
    {
        $travelRequest = new TravelRequest();
        $this->assertEquals('id', $travelRequest->getKeyName());
    }

    public function test_timestamps_enabled(): void
    {
        $travelRequest = new TravelRequest();
        $this->assertTrue($travelRequest->usesTimestamps());
    }

    public function test_scope_by_destination_case_insensitive(): void
    {
        TravelRequest::factory()->create(['destination' => 'São Paulo']);
        TravelRequest::factory()->create(['destination' => 'PARIS']);
        TravelRequest::factory()->create(['destination' => 'london']);

        $pauloRequests = TravelRequest::byDestination('paulo')->get();
        $parisRequests = TravelRequest::byDestination('paris')->get();
        $londonRequests = TravelRequest::byDestination('LONDON')->get();

        $this->assertCount(1, $pauloRequests);
        $this->assertCount(1, $parisRequests);
        $this->assertCount(1, $londonRequests);
    }

    public function test_model_attributes_assignment(): void
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'requester_name' => 'João Silva',
            'destination' => 'Tokyo',
            'departure_date' => '2024-06-15',
            'return_date' => '2024-06-25',
            'status' => TravelRequestStatusEnum::REQUESTED,
            'notes' => 'Viagem de negócios'
        ];

        $travelRequest = TravelRequest::create($data);

        $this->assertEquals($user->id, $travelRequest->user_id);
        $this->assertEquals('João Silva', $travelRequest->requester_name);
        $this->assertEquals('Tokyo', $travelRequest->destination);
        $this->assertEquals('2024-06-15', $travelRequest->departure_date->format('Y-m-d'));
        $this->assertEquals('2024-06-25', $travelRequest->return_date->format('Y-m-d'));
        $this->assertEquals(TravelRequestStatusEnum::REQUESTED, $travelRequest->status);
        $this->assertEquals('Viagem de negócios', $travelRequest->notes);
    }
}