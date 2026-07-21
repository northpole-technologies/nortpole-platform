<?php

namespace Platform\Contracts;

interface ModuleInterface
{
    public function register(): void;

    public function boot(): void;
}