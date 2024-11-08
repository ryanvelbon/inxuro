<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_policies_index_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/policies');

        $response->assertStatus(200);
    }

    public function test_policies_create_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/policies/create')->assertStatus(200);
    }
}
