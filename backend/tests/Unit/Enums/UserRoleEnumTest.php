<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRoleEnum;
use PHPUnit\Framework\TestCase;

class UserRoleEnumTest extends TestCase
{
    public function test_enum_cases(): void
    {
        $this->assertEquals('admin', UserRoleEnum::ADMIN->value);
        $this->assertEquals('user', UserRoleEnum::USER->value);
    }

    public function test_label_returns_correct_values(): void
    {
        $this->assertEquals('Administrador', UserRoleEnum::ADMIN->label());
        $this->assertEquals('Usuário', UserRoleEnum::USER->label());
    }

    public function test_can_admin_returns_true_for_admin_role(): void
    {
        $this->assertTrue(UserRoleEnum::ADMIN->canAdmin());
    }

    public function test_can_admin_returns_false_for_user_role(): void
    {
        $this->assertFalse(UserRoleEnum::USER->canAdmin());
    }

    public function test_can_user_returns_true_for_user_role(): void
    {
        $this->assertTrue(UserRoleEnum::USER->canUser());
    }

    public function test_can_user_returns_false_for_admin_role(): void
    {
        $this->assertFalse(UserRoleEnum::ADMIN->canUser());
    }

    public function test_enum_cases_count(): void
    {
        $cases = UserRoleEnum::cases();
        $this->assertCount(2, $cases);
    }

    public function test_enum_from_value(): void
    {
        $this->assertEquals(UserRoleEnum::ADMIN, UserRoleEnum::from('admin'));
        $this->assertEquals(UserRoleEnum::USER, UserRoleEnum::from('user'));
    }

    public function test_enum_try_from_valid_value(): void
    {
        $this->assertEquals(UserRoleEnum::ADMIN, UserRoleEnum::tryFrom('admin'));
        $this->assertEquals(UserRoleEnum::USER, UserRoleEnum::tryFrom('user'));
    }

    public function test_enum_try_from_invalid_value(): void
    {
        $this->assertNull(UserRoleEnum::tryFrom('invalid'));
    }

    public function test_enum_values_are_strings(): void
    {
        foreach (UserRoleEnum::cases() as $case) {
            $this->assertIsString($case->value);
        }
    }

    public function test_enum_names(): void
    {
        $this->assertEquals('ADMIN', UserRoleEnum::ADMIN->name);
        $this->assertEquals('USER', UserRoleEnum::USER->name);
    }
}