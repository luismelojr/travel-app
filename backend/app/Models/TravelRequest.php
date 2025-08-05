<?php

namespace App\Models;

use App\Enums\TravelRequestStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class TravelRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requester_name',
        'destination',
        'departure_date',
        'return_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
        'status' => TravelRequestStatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByStatus(Builder $query, TravelRequestStatusEnum|string $status): Builder
    {
        $statusValue = $status instanceof TravelRequestStatusEnum ? $status->value : $status;
        return $query->where('status', $statusValue);
    }

    public function scopeByDestination(Builder $query, string $destination): Builder
    {
        return $query->where('destination', 'like', '%' . $destination . '%');
    }

    public function scopeByDateRange(Builder $query, ?Carbon $startDate = null, ?Carbon $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('departure_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('departure_date', '<=', $endDate);
        }
        
        return $query;
    }

    public function scopeByRequestDateRange(Builder $query, ?Carbon $startDate = null, ?Carbon $endDate = null): Builder
    {
        if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query;
    }

    public function getDurationInDays(): int
    {
        return $this->departure_date->diffInDays($this->return_date) + 1;
    }

    public function canBeApproved(): bool
    {
        return $this->status->canBeApproved();
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }
}
