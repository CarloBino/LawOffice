<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_creation_is_recorded_in_activity_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'full_name' => 'Ralph Medino',
                'client_type' => 'Individual',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'Client created',
        ]);

        $this->actingAs($user)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('Client created')
            ->assertSee('Created client Ralph Medino.');
    }
}
