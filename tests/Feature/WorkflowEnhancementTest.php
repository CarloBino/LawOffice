<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Hearing;
use App\Models\LegalCase;
use App\Models\OfficeExpense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowEnhancementTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_print_pages_and_exports_are_available(): void
    {
        $user = User::factory()->create(['role' => 'staff']);
        $client = Client::create(['full_name' => 'Ralph Medino', 'client_type' => 'Individual']);
        $case = LegalCase::create([
            'case_number' => 'CASE-001',
            'case_title' => 'Criminal Case',
            'client_id' => $client->id,
            'case_status' => 'New',
            'priority_level' => 'High',
        ]);
        Billing::create([
            'case_id' => $case->id,
            'total_amount' => 10000,
            'amount_paid' => 2500,
            'balance' => 7500,
            'payment_status' => 'Partial',
        ]);
        Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => '2026-06-01',
            'court_branch' => 'RTC Branch 8 - Tacloban City, Leyte',
            'hearing_status' => 'Scheduled',
        ]);
        OfficeExpense::create([
            'expense_type' => 'Rent Fee',
            'amount' => 5000,
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($user)
            ->get(route('clients.index', ['balance' => 'with_balance']))
            ->assertOk()
            ->assertSee('Ralph Medino');

        $this->actingAs($user)
            ->get(route('cases.print', $case))
            ->assertOk()
            ->assertSee('Case Summary');

        $this->actingAs($user)
            ->get(route('clients.statement', $client))
            ->assertOk()
            ->assertSee('Client Statement');

        $this->actingAs($user)
            ->get(route('billings.export'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('office-expenses.export'))
            ->assertOk();
    }
}
