<?php

namespace Tests\Feature;

use App\Models\TripPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PostShareSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_share_exposes_only_whitelisted_fields(): void
    {
        $post = TripPost::factory()->shared()->create(['caption' => 'first ice of the year']);

        $response = $this->get('/p/'.$post->share_token);

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Posts/Share')
            ->where('post.id', $post->id)
            ->where('post.caption', 'first ice of the year')
            // Internal columns must not leak to unauthenticated visitors.
            ->missing('post.share_token')
            ->missing('post.user_id')
            ->missing('post.trip_id')
            ->missing('post.lake_id')
            ->missing('post.is_public')
            // The author is exposed as a safe sub-object (no email).
            ->where('post.user.username', $post->user->username)
            ->missing('post.user.email')
        );
    }

    public function test_private_post_share_is_not_found(): void
    {
        $post = TripPost::factory()->private()->create(['share_token' => 'tok_private_123']);

        $this->get('/p/'.$post->share_token)->assertNotFound();
    }

    public function test_unknown_token_is_not_found(): void
    {
        $this->get('/p/does-not-exist')->assertNotFound();
    }
}
