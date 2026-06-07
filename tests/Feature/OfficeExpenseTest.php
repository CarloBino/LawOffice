<?php

namespace Tests\Feature;

use App\Models\OfficeExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficeExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_and_view_office_expense(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('office-expenses.store'), [
                'expense_type' => 'Rent Fee',
                'description' => 'May office rent',
                'amount' => 25000,
                'due_date' => '2026-05-31',
                'payment_status' => 'Unpaid',
                'notes' => 'Monthly office expense',
            ])
            ->assertRedirect();

        $expense = OfficeExpense::firstOrFail();

        $this->actingAs($user)
            ->get(route('office-expenses.index'))
            ->assertOk()
            ->assertSee('Office Expenses')
            ->assertSee('Rent Fee')
            ->assertSee('25,000.00');

        $this->actingAs($user)
            ->get(route('office-expenses.show', $expense))
            ->assertOk()
            ->assertSee('May office rent');
    }

    public function test_user_can_mark_office_expense_paid_and_unpaid(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $expense = OfficeExpense::create([
            'expense_type' => 'WiFi Fee',
            'amount' => 2000,
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($user)
            ->patch(route('office-expenses.toggle-paid', $expense))
            ->assertRedirect();

        $expense->refresh();
        $this->assertSame('Paid', $expense->payment_status);
        $this->assertNotNull($expense->payment_date);

        $this->actingAs($user)
            ->patch(route('office-expenses.toggle-paid', $expense))
            ->assertRedirect();

        $expense->refresh();
        $this->assertSame('Unpaid', $expense->payment_status);
        $this->assertNull($expense->payment_date);
    }

    public function test_staff_cannot_access_office_expenses(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('office-expenses.index'))
            ->assertForbidden();
    }
}
