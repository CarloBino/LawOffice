<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\Hearing;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_profile_shows_connected_work_and_account_summary(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'full_name' => 'Ralph Medino',
            'contact_number' => '09171234567',
            'email' => 'ralph@example.com',
            'address' => 'Manila',
            'client_type' => 'individual',
        ]);
        $case = LegalCase::create([
            'case_number' => '12-32-12',
            'case_title' => 'Criminal Case',
            'case_status' => 'Active',
            'client_id' => $client->id,
        ]);
        $billing = Billing::create([
            'case_id' => $case->id,
            'acceptance_fee' => 10000,
            'appearance_fee' => 10000,
            'total_amount' => 20000,
            'amount_paid' => 0,
            'balance' => 20000,
            'payment_status' => 'Unpaid',
        ]);
        $billing->payments()->create([
            'amount' => 5000,
            'date_received' => '2026-05-25',
            'official_receipt_number' => 'OR-CLIENT-1',
        ]);
        $billing->recalculatePaymentTotals();
        Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => now()->addWeek()->toDateString(),
            'hearing_time' => '09:00:00',
            'court_venue' => 'RTC Branch 1',
            'hearing_purpose' => 'Arraignment',
        ]);

        $this->actingAs($user)
            ->get(route('clients.show', $client))
            ->assertOk()
            ->assertSee('Client profile')
            ->assertSee('12-32-12')
            ->assertSee('Criminal Case')
            ->assertSee('This client total comes from the billings recorded under each case.')
            ->assertSee('15,000.00')
            ->assertSee('OR-CLIENT-1')
            ->assertSee('RTC Branch 1');
    }

    public function test_clients_index_shows_work_and_balance_summary(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'full_name' => 'John Paul Mendiola',
            'client_type' => 'individual',
        ]);
        $case = LegalCase::create([
            'case_number' => 'CLIENT-001',
            'case_title' => 'Civil Matter',
            'case_status' => 'Active',
            'client_id' => $client->id,
        ]);
        Billing::create([
            'case_id' => $case->id,
            'acceptance_fee' => 10000,
            'total_amount' => 10000,
            'amount_paid' => 2500,
            'balance' => 7500,
            'payment_status' => 'Partial',
        ]);

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('John Paul Mendiola')
            ->assertSee('Manage')
            ->assertSee('10,000.00')
            ->assertSee('7,500.00');
    }
}
