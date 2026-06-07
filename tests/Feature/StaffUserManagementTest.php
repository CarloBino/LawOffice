<?php

namespace Tests\Feature;

use App\Models\Lawyer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
                'role' => 'staff',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('staff-users.index'));

        $this->assertDatabaseHas('users', [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => 'staff',
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

        $target = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('staff-users.edit', $target))
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('staff-users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'role' => 'admin',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($staff)
            ->delete(route('staff-users.destroy', $target))
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

    public function test_admin_created_lawyer_account_gets_linked_to_lawyer_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('staff-users.store'), [
                'name' => 'Alfred Verona',
                'email' => 'alfred@example.com',
                'role' => 'lawyer',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('staff-users.index'));

        $user = User::where('email', 'alfred@example.com')->firstOrFail();

        $this->assertDatabaseHas('lawyers', [
            'user_id' => $user->id,
            'full_name' => 'Alfred Verona',
            'email' => 'alfred@example.com',
            'status' => 'Active',
        ]);
    }

    public function test_admin_created_lawyer_account_links_existing_lawyer_profile(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $lawyer = Lawyer::create([
            'full_name' => 'Alfred Verona',
            'email' => 'old@example.com',
            'status' => 'Active',
        ]);

        $this->actingAs($admin)
            ->post(route('staff-users.store'), [
                'name' => 'Alfred Verona',
                'email' => 'alfred@example.com',
                'role' => 'lawyer',
                'status' => 'active',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('staff-users.index'));

        $user = User::where('email', 'alfred@example.com')->firstOrFail();

        $this->assertDatabaseHas('lawyers', [
            'id' => $lawyer->id,
            'user_id' => $user->id,
            'full_name' => 'Alfred Verona',
            'email' => 'alfred@example.com',
        ]);
        $this->assertSame(1, Lawyer::where('full_name', 'Alfred Verona')->count());
    }

    public function test_admin_can_update_account_role_status_and_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $staff = User::factory()->create(['role' => 'staff', 'status' => 'active']);

        $this->actingAs($admin)
            ->patch(route('staff-users.update', $staff), [
                'name' => 'Updated Lawyer',
                'email' => 'updated-lawyer@example.com',
                'role' => 'lawyer',
                'status' => 'inactive',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('staff-users.index'));

        $staff->refresh();

        $this->assertSame('lawyer', $staff->role);
        $this->assertSame('inactive', $staff->status);
        $this->assertTrue(Hash::check('new-password', $staff->password));
        $this->assertDatabaseHas('lawyers', [
            'user_id' => $staff->id,
            'full_name' => 'Updated Lawyer',
            'email' => 'updated-lawyer@example.com',
            'status' => 'Inactive',
        ]);
    }

    public function test_last_active_administrator_cannot_be_demoted_or_deactivated(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->from(route('staff-users.edit', $admin))
            ->patch(route('staff-users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('account');

        $this->assertSame('admin', $admin->refresh()->role);

        $this->actingAs($admin)
            ->from(route('staff-users.edit', $admin))
            ->patch(route('staff-users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'admin',
                'status' => 'inactive',
            ])
            ->assertSessionHasErrors('account');

        $this->assertSame('active', $admin->refresh()->status);
    }

    public function test_admin_can_delete_another_staff_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)
            ->delete(route('staff-users.destroy', $staff))
            ->assertRedirect(route('staff-users.index'));

        $this->assertNull($staff->fresh());
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

    public function test_an_existing_session_is_ended_when_account_becomes_inactive(): void
    {
        $user = User::factory()->create([
            'role' => 'staff',
            'status' => 'inactive',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
