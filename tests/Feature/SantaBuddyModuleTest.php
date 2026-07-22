<?php

namespace Tests\Feature;

use Tests\TestCase;

class SantaBuddyModuleTest extends TestCase
{
    public function test_santa_buddy_web_page_loads_successfully(): void
    {
        $response = $this->get('/santa-buddy');

        $response
            ->assertOk()
            ->assertSee('SantaBuddy')
            ->assertSee('installed, enabled and running');
    }

    public function test_santa_buddy_api_status_returns_expected_data(): void
    {
        $response = $this->getJson('/api/santa-buddy/status');

        $response
            ->assertOk()
            ->assertJson([
                'module' => 'SantaBuddy',
                'status' => 'running',
                'version' => '1.0.0',
            ]);
    }
}