<?php

namespace Tests\Unit\Enums;

use App\Enums\TravelRequestStatusEnum;
use PHPUnit\Framework\TestCase;

class TravelRequestStatusEnumTest extends TestCase
{
    public function test_enum_cases(): void
    {
        $this->assertEquals('requested', TravelRequestStatusEnum::REQUESTED->value);
        $this->assertEquals('approved', TravelRequestStatusEnum::APPROVED->value);
        $this->assertEquals('cancelled', TravelRequestStatusEnum::CANCELLED->value);
    }

    public function test_label_returns_correct_values(): void
    {
        $this->assertEquals('Solicitado', TravelRequestStatusEnum::REQUESTED->label());
        $this->assertEquals('Aprovado', TravelRequestStatusEnum::APPROVED->label());
        $this->assertEquals('Cancelado', TravelRequestStatusEnum::CANCELLED->label());
    }

    public function test_can_be_approved_returns_true_for_requested(): void
    {
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->canBeApproved());
    }

    public function test_can_be_approved_returns_false_for_approved(): void
    {
        $this->assertFalse(TravelRequestStatusEnum::APPROVED->canBeApproved());
    }

    public function test_can_be_approved_returns_false_for_cancelled(): void
    {
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->canBeApproved());
    }

    public function test_can_be_cancelled_returns_true_for_requested(): void
    {
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->canBeCancelled());
    }

    public function test_can_be_cancelled_returns_true_for_approved(): void
    {
        $this->assertTrue(TravelRequestStatusEnum::APPROVED->canBeCancelled());
    }

    public function test_can_be_cancelled_returns_false_for_cancelled(): void
    {
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->canBeCancelled());
    }

    public function test_is_active_returns_true_for_requested(): void
    {
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->isActive());
    }

    public function test_is_active_returns_true_for_approved(): void
    {
        $this->assertTrue(TravelRequestStatusEnum::APPROVED->isActive());
    }

    public function test_is_active_returns_false_for_cancelled(): void
    {
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->isActive());
    }

    public function test_enum_cases_count(): void
    {
        $cases = TravelRequestStatusEnum::cases();
        $this->assertCount(3, $cases);
    }

    public function test_enum_from_value(): void
    {
        $this->assertEquals(TravelRequestStatusEnum::REQUESTED, TravelRequestStatusEnum::from('requested'));
        $this->assertEquals(TravelRequestStatusEnum::APPROVED, TravelRequestStatusEnum::from('approved'));
        $this->assertEquals(TravelRequestStatusEnum::CANCELLED, TravelRequestStatusEnum::from('cancelled'));
    }

    public function test_enum_try_from_valid_value(): void
    {
        $this->assertEquals(TravelRequestStatusEnum::REQUESTED, TravelRequestStatusEnum::tryFrom('requested'));
        $this->assertEquals(TravelRequestStatusEnum::APPROVED, TravelRequestStatusEnum::tryFrom('approved'));
        $this->assertEquals(TravelRequestStatusEnum::CANCELLED, TravelRequestStatusEnum::tryFrom('cancelled'));
    }

    public function test_enum_try_from_invalid_value(): void
    {
        $this->assertNull(TravelRequestStatusEnum::tryFrom('invalid'));
    }

    public function test_enum_values_are_strings(): void
    {
        foreach (TravelRequestStatusEnum::cases() as $case) {
            $this->assertIsString($case->value);
        }
    }

    public function test_enum_names(): void
    {
        $this->assertEquals('REQUESTED', TravelRequestStatusEnum::REQUESTED->name);
        $this->assertEquals('APPROVED', TravelRequestStatusEnum::APPROVED->name);
        $this->assertEquals('CANCELLED', TravelRequestStatusEnum::CANCELLED->name);
    }

    public function test_from_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        TravelRequestStatusEnum::from('invalid');
    }

    public function test_all_cases_have_labels(): void
    {
        foreach (TravelRequestStatusEnum::cases() as $case) {
            $this->assertIsString($case->label());
            $this->assertNotEmpty($case->label());
        }
    }

    public function test_transition_logic_consistency(): void
    {
        // REQUESTED pode ser aprovado e cancelado
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->canBeApproved());
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->canBeCancelled());
        $this->assertTrue(TravelRequestStatusEnum::REQUESTED->isActive());

        // APPROVED pode ser cancelado mas não re-aprovado
        $this->assertFalse(TravelRequestStatusEnum::APPROVED->canBeApproved());
        $this->assertTrue(TravelRequestStatusEnum::APPROVED->canBeCancelled());
        $this->assertTrue(TravelRequestStatusEnum::APPROVED->isActive());

        // CANCELLED é estado final
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->canBeApproved());
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->canBeCancelled());
        $this->assertFalse(TravelRequestStatusEnum::CANCELLED->isActive());
    }

    public function test_all_enum_methods_exist(): void
    {
        $case = TravelRequestStatusEnum::REQUESTED;
        
        $this->assertTrue(method_exists($case, 'label'));
        $this->assertTrue(method_exists($case, 'canBeApproved'));
        $this->assertTrue(method_exists($case, 'canBeCancelled'));
        $this->assertTrue(method_exists($case, 'isActive'));
    }
}