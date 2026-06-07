<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_creation_is_recorded_in_activity_log(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

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

    public function test_only_administrators_can_view_the_activity_log(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('activity-logs.index'))
            ->assertForbidden();
    }
}
