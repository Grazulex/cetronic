<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Mcamara\LaravelLocalization\LaravelLocalization;

abstract class TestCase extends BaseTestCase
{

    protected function tearDown(): void
    {
        putenv(LaravelLocalization::ENV_ROUTE_KEY);
        parent::tearDown();
    }

    protected function refreshApplicationWithLocale($locale): void
    {
        self::tearDown();
        putenv(LaravelLocalization::ENV_ROUTE_KEY.'='.$locale);
        self::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [

        ];
    }
}
