<?php

namespace App\Enums;

enum TravelRequestStatusEnum: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Solicitado',
            self::APPROVED => 'Aprovado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function canBeApproved(): bool
    {
        return $this === self::REQUESTED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::REQUESTED, self::APPROVED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::REQUESTED, self::APPROVED]);
    }
}