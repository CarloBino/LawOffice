<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingTogglePaidTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_billing_paid_and_unpaid(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'BILL-001',
            'case_title' => 'Billing Matter',
        ]);
        $billing = Billing::create([
            'case_id' => $case->id,
            'professional_fee' => 1000,
            'filing_fee' => 200,
            'other_expenses' => 50,
            'total_amount' => 1250,
            'amount_paid' => 250,
            'balance' => 1000,
            'payment_status' => 'Partial',
        ]);

        $this->actingAs($user)
            ->patch(route('billings.toggle-paid', $billing))
            ->assertRedirect();

        $billing->refresh();
        $this->assertSame('Paid', $billing->payment_status);
        $this->assertEquals(1250, $billing->amount_paid);
        $this->assertEquals(0, $billing->balance);
        $this->assertDatabaseHas('billing_payments', [
            'billing_id' => $billing->id,
            'amount' => 1250,
            'notes' => 'Marked paid from billing shortcut.',
        ]);

        $this->actingAs($user)
            ->patch(route('billings.toggle-paid', $billing))
            ->assertRedirect();

        $billing->refresh();
        $this->assertSame('Unpaid', $billing->payment_status);
        $this->assertEquals(0, $billing->amount_paid);
        $this->assertEquals(1250, $billing->balance);
        $this->assertDatabaseMissing('billing_payments', [
            'billing_id' => $billing->id,
        ]);
    }

    public function test_user_can_create_billing_with_client_fee_categories(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'BILL-002',
            'case_title' => 'Client Fee Matter',
        ]);

        $this->actingAs($user)
            ->post(route('billings.store'), [
                'case_id' => $case->id,
                'acceptance_fee' => 1000,
                'appearance_fee' => 500,
                'pleading_fee' => 750,
                'notarial_fee' => 250,
                'success_fee' => 3000,
                'retainer_fee' => 1500,
                'other_fees' => 125,
                'payment_amount' => 2000,
                'payment_date_received' => '2026-05-24',
                'payment_official_receipt_number' => 'OR-1001',
            ])
            ->assertRedirect();

        $billing = Billing::where('case_id', $case->id)->firstOrFail();

        $this->assertEquals(7125, $billing->total_amount);
        $this->assertEquals(2000, $billing->amount_paid);
        $this->assertEquals(5125, $billing->balance);
        $this->assertSame('Partial', $billing->payment_status);
        $this->assertSame('2026-05-24', $billing->payment_date);
        $this->assertDatabaseHas('billing_payments', [
            'billing_id' => $billing->id,
            'amount' => 2000,
            'date_received' => '2026-05-24',
            'official_receipt_number' => 'OR-1001',
        ]);
    }

    public function test_user_can_record_multiple_payments_received_on_different_dates(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'BILL-003',
            'case_title' => 'Installment Matter',
        ]);
        $billing = Billing::create([
            'case_id' => $case->id,
            'acceptance_fee' => 3000,
            'appearance_fee' => 1000,
            'total_amount' => 4000,
            'amount_paid' => 0,
            'balance' => 4000,
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($user)
            ->post(route('billings.payments.store', $billing), [
                'amount' => 1500,
                'date_received' => '2026-05-01',
                'official_receipt_number' => 'OR-2001',
            ])
            ->assertRedirect(route('billings.show', $billing));

        $this->actingAs($user)
            ->post(route('billings.payments.store', $billing), [
                'amount' => 2500,
                'date_received' => '2026-05-24',
                'official_receipt_number' => 'OR-2002',
            ])
            ->assertRedirect(route('billings.show', $billing));

        $billing->refresh();
        $this->assertEquals(4000, $billing->amount_paid);
        $this->assertEquals(0, $billing->balance);
        $this->assertSame('Paid', $billing->payment_status);
        $this->assertSame('2026-05-24', $billing->payment_date);
        $this->assertDatabaseCount('billing_payments', 2);
    }

    public function test_billing_charges_cannot_be_edited_after_creation(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'BILL-004',
            'case_title' => 'Locked Billing Matter',
        ]);
        $billing = Billing::create([
            'case_id' => $case->id,
            'acceptance_fee' => 1000,
            'total_amount' => 1000,
            'amount_paid' => 0,
            'balance' => 1000,
            'payment_status' => 'Unpaid',
        ]);

        $this->actingAs($user)
            ->put(route('billings.update', $billing), [
                'case_id' => $case->id,
                'acceptance_fee' => 5000,
                'appearance_fee' => 5000,
            ])
            ->assertRedirect(route('billings.edit', $billing));

        $billing->refresh();
        $this->assertEquals(1000, $billing->acceptance_fee);
        $this->assertEquals(1000, $billing->total_amount);
        $this->assertEquals(1000, $billing->balance);
    }

    public function test_billing_create_can_be_limited_to_a_case(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'BILL-005',
            'case_title' => 'Selected Matter',
        ]);

        $this->actingAs($user)
            ->get(route('billings.create', ['case_id' => $case->id]))
            ->assertOk()
            ->assertSee('BILL-005 - Selected Matter');
    }
}
