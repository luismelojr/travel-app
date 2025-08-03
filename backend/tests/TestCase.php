<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Configurar JWT para testes
        config([
            'jwt.secret' => 'test-jwt-secret-key-for-testing-very-long-key-to-ensure-security',
            'jwt.ttl' => 60,
            'jwt.refresh_ttl' => 20160,
            'jwt.algo' => 'HS256',
            'jwt.blacklist_enabled' => false, // Simplificar para testes
        ]);
    }
}
