<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure we're using the testing database and proper cache settings
        config([
            'cache.default' => 'array',
            'queue.default' => 'sync',
            'session.driver' => 'array',
            'auth.defaults.guard' => 'web',
        ]);

        // Start the session for each test
        $this->withSession([]);

        // Disable CSRF for API testing
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
    }
}
