<?php

declare(strict_types=1);

namespace Tests\Fixtures\Runtime;

use Illuminate\Support\ServiceProvider;

final class SecondaryRecordingServiceProvider extends ServiceProvider
{
    public const BINDING = 'northpole.runtime.secondary-provider-stage-test';

    public function register(): void
    {
        $this->app->singleton(
            self::BINDING,
            static fn (): string => 'secondary-provider-registered'
        );
    }
}