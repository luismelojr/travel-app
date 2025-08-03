<?php

namespace Tests\Unit\Rules;

use App\Rules\PasswordRule;
use Tests\TestCase;

class PasswordRuleTest extends TestCase
{
    private PasswordRule $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new PasswordRule();
    }

    /**
     * Test valid password passes validation
     */
    public function test_valid_password_passes(): void
    {
        $failCallback = function ($message) {
            $this->fail("Password validation should pass but failed with: $message");
        };

        $this->rule->validate('password', 'MinhaSenh@123', $failCallback);
        
        // If we reach this point, validation passed
        $this->assertTrue(true);
    }

    /**
     * Test password shorter than 8 characters fails
     */
    public function test_short_password_fails(): void
    {
        $failMessage = null;
        $failCallback = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('password', 'Abc@1', $failCallback);
        
        $this->assertEquals('A senha deve ter pelo menos 8 caracteres.', $failMessage);
    }

    /**
     * Test password without lowercase letter fails
     */
    public function test_password_without_lowercase_fails(): void
    {
        $failMessage = null;
        $failCallback = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('password', 'MINHASENHA@123', $failCallback);
        
        $this->assertEquals('A senha deve conter pelo menos uma letra minúscula.', $failMessage);
    }

    /**
     * Test password without uppercase letter fails
     */
    public function test_password_without_uppercase_fails(): void
    {
        $failMessage = null;
        $failCallback = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('password', 'minhasenha@123', $failCallback);
        
        $this->assertEquals('A senha deve conter pelo menos uma letra maiúscula.', $failMessage);
    }

    /**
     * Test password without number fails
     */
    public function test_password_without_number_fails(): void
    {
        $failMessage = null;
        $failCallback = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('password', 'MinhaSenha@', $failCallback);
        
        $this->assertEquals('A senha deve conter pelo menos um número.', $failMessage);
    }

    /**
     * Test password without special character fails
     */
    public function test_password_without_special_character_fails(): void
    {
        $failMessage = null;
        $failCallback = function ($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('password', 'MinhaSenha123', $failCallback);
        
        $this->assertEquals('A senha deve conter pelo menos um símbolo especial.', $failMessage);
    }

    /**
     * Test various valid password combinations
     */
    public function test_various_valid_passwords(): void
    {
        $validPasswords = [
            'Password123!',
            'MinhaSenh@456',
            'Test#Password789',
            'SecureP@ssw0rd',
            'Abc123!@#',
            'Complex$Pass1'
        ];

        foreach ($validPasswords as $password) {
            $failCallback = function ($message) use ($password) {
                $this->fail("Password '$password' should be valid but failed with: $message");
            };

            $this->rule->validate('password', $password, $failCallback);
        }

        // If we reach this point, all passwords passed
        $this->assertTrue(true);
    }

    /**
     * Test edge case with exactly 8 characters
     */
    public function test_exactly_eight_characters_password(): void
    {
        $failCallback = function ($message) {
            $this->fail("8-character password should pass but failed with: $message");
        };

        $this->rule->validate('password', 'Pass123!', $failCallback);
        
        // Should pass - exactly 8 characters with all requirements
        $this->assertTrue(true);
    }

    /**
     * Test various special characters are accepted
     */
    public function test_various_special_characters(): void
    {
        $specialChars = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '+', '='];
        
        foreach ($specialChars as $char) {
            $password = "Password123{$char}";
            
            $failCallback = function ($message) use ($password, $char) {
                $this->fail("Password with special char '{$char}' should pass but failed with: $message");
            };

            $this->rule->validate('password', $password, $failCallback);
        }

        // If we reach this point, all special characters passed
        $this->assertTrue(true);
    }
}