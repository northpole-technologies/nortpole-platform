<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Support\InteractsWithTenantPermissions;

abstract class TestCase extends BaseTestCase
{
    use InteractsWithTenantPermissions;
}
