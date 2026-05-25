<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Client;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_direct_matches_across_records(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['full_name' => 'Ralph Medino', 'email' => 'ralph@example.com']);
        $case = LegalCase::create([
            'case_number' => '12-32-12',
            'case_title' => 'Criminal Case',
            'client_id' => $client->id,
        ]);
        $billing = Billing::create([
            'case_id' => $case->id,
            'total_amount' => 80000,
            'amount_paid' => 0,
            'balance' => 80000,
            'payment_status' => 'Unpaid',
        ]);
        $billing->payments()->create([
            'amount' => 25000,
            'date_received' => '2026-06-30',
            'official_receipt_number' => '1122',
        ]);

        $this->actingAs($user)
            ->get(route('search.index', ['q' => 'Ralph']))
            ->assertOk()
            ->assertSee('Client')
            ->assertSee('Ralph Medino')
            ->assertDontSee('12-32-12 - Criminal Case');

        $this->actingAs($user)
            ->get(route('search.index', ['q' => '12-32-12']))
            ->assertOk()
            ->assertSee('Case')
            ->assertSee('12-32-12 - Criminal Case');

        $this->actingAs($user)
            ->get(route('search.index', ['q' => '1122']))
            ->assertOk()
            ->assertSee('Payment')
            ->assertSee('Receipt 1122');
    }
}
