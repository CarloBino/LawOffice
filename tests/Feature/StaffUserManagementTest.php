<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('staff-users.index'))
            ->assertOk()
            ->assertSee('Staff Accounts');

        $this->actingAs($admin)
            ->post(route('staff-users.store'), [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'role' => 'secretary',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('staff-users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => 'secretary',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'Staff account created',
        ]);
    }

    public function test_non_admin_cannot_manage_staff_accounts(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('staff-users.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->post(route('staff-users.store'), [
                'name' => 'Blocked User',
                'email' => 'blocked@example.com',
                'role' => 'staff',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => 'blocked@example.com',
        ]);
    }

    public function test_inactive_users_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'password',
            'status' => 'inactive',
        ]);

        $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
