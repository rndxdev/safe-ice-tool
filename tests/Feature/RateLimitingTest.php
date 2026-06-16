<?php

namespace Tests\Feature;

use App\Models\IceReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_rate_limited(): void
    {
        // throttle:5,1 — the 6th attempt within the window is rejected. The
        // limiter runs before validation, so empty payloads still count.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/register', []);
        }

        $this->post('/register', [])->assertStatus(429);
    }

    public function test_voting_is_rate_limited_per_user(): void
    {
        $user = User::factory()->create();
        $report = IceReport::factory()->create();

        // throttle:30,1 on downvote — the 31st request trips the limit.
        for ($i = 0; $i < 30; $i++) {
            $this->actingAs($user)->post(route('reports.downvote', $report));
        }

        $this->actingAs($user)
            ->post(route('reports.downvote', $report))
            ->assertStatus(429);
    }
}
