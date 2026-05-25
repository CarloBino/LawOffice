<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\OfficeExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerAccessScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_lawyer_only_sees_assigned_work(): void
    {
        $user = User::factory()->create(['role' => 'lawyer']);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'full_name' => 'Attorney One',
            'email' => 'attorney@example.com',
            'status' => 'Active',
        ]);
        $otherLawyer = Lawyer::create([
            'full_name' => 'Attorney Two',
            'email' => 'other-attorney@example.com',
            'status' => 'Active',
        ]);

        $assignedClient = Client::create(['full_name' => 'Assigned Client', 'client_type' => 'Individual']);
        $otherClient = Client::create(['full_name' => 'Other Client', 'client_type' => 'Individual']);

        $assignedCase = LegalCase::create([
            'case_number' => 'A-001',
            'case_title' => 'Assigned Matter',
            'client_id' => $assignedClient->id,
            'assigned_lawyer_id' => $lawyer->id,
            'case_status' => 'Open',
            'priority_level' => 'High',
        ]);
        $otherCase = LegalCase::create([
            'case_number' => 'B-001',
            'case_title' => 'Other Matter',
            'client_id' => $otherClient->id,
            'assigned_lawyer_id' => $otherLawyer->id,
            'case_status' => 'Open',
            'priority_level' => 'High',
        ]);

        Billing::create([
            'case_id' => $assignedCase->id,
            'total_amount' => 10000,
            'amount_paid' => 0,
            'balance' => 10000,
            'payment_status' => 'Unpaid',
        ]);
        Billing::create([
            'case_id' => $otherCase->id,
            'total_amount' => 20000,
            'amount_paid' => 0,
            'balance' => 20000,
            'payment_status' => 'Unpaid',
        ]);
        Hearing::create([
            'case_id' => $assignedCase->id,
            'hearing_date' => now()->addDay()->toDateString(),
            'hearing_status' => 'Scheduled',
        ]);
        Hearing::create([
            'case_id' => $otherCase->id,
            'hearing_date' => now()->addDay()->toDateString(),
            'hearing_status' => 'Scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('cases.index'))
            ->assertOk()
            ->assertSee('Assigned Matter')
            ->assertDontSee('Other Matter');

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Assigned Client')
            ->assertDontSee('Other Client');

        $this->actingAs($user)
            ->get(route('billings.index'))
            ->assertOk()
            ->assertSee('10,000.00')
            ->assertDontSee('20,000.00');

        $this->actingAs($user)
            ->get(route('hearings.index'))
            ->assertOk()
            ->assertSee('Assigned Matter')
            ->assertDontSee('Other Matter');

        $this->actingAs($user)
            ->get(route('cases.show', $otherCase))
            ->assertForbidden();
    }

    public function test_lawyer_cannot_access_office_expenses_or_create_billings(): void
    {
        $user = User::factory()->create(['role' => 'lawyer']);
        Lawyer::create([
            'user_id' => $user->id,
            'full_name' => 'Attorney One',
            'email' => 'attorney@example.com',
            'status' => 'Active',
        ]);
        $expense = OfficeExpense::create([
            'expense_type' => 'Rent Fee',
            'amount' => 1000,
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($user)
            ->get(route('office-expenses.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('office-expenses.show', $expense))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('billings.create'))
            ->assertForbidden();
    }
}
