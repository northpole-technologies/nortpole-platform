<?php

namespace Tests\Fixtures\Runtime;

use Illuminate\Support\ServiceProvider;

final class RecordingServiceProvider extends ServiceProvider
{
    public const BINDING = 'northpole.runtime.provider-stage-test';

    public function register(): void
    {
        $this->app->singleton(
            self::BINDING,
            static fn (): string => 'provider-registered'
        );
    }
}