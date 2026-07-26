<?php

declare(strict_types=1);

namespace Northpole\Core\Registration\Contracts;

use Illuminate\Contracts\Foundation\Application;

interface ServiceRegistrar
{
    public function register(
        Application $application
    ): void;
}
