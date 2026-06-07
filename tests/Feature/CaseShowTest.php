<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Billing;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_case_show_page_loads_with_related_records(): void
    {
        $user = User::factory()->create();
        $client = Client::create([
            'full_name' => 'Test Client',
            'client_type' => 'individual',
        ]);
        $case = LegalCase::create([
            'case_number' => 'CV-001',
            'case_title' => 'Test Matter',
            'client_id' => $client->id,
            'case_status' => 'New',
            'priority_level' => 'Low',
        ]);
        Billing::create([
            'case_id' => $case->id,
            'acceptance_fee' => 5000,
            'total_amount' => 5000,
            'amount_paid' => 1000,
            'balance' => 4000,
            'payment_status' => 'Partial',
        ]);

        $response = $this->actingAs($user)->get(route('cases.show', $case));

        $response->assertOk();
        $response->assertSee('Test Matter');
        $response->assertSee('Financial summary');
        $response->assertSee('Totals are calculated from billing records for CV-001.');
        $response->assertSee('Total Billed');
        $response->assertSee('Total Paid');
        $response->assertSee('Outstanding Balance');
        $response->assertSee('4,000.00');
    }

    public function test_case_form_uses_controlled_case_type_dropdown(): void
    {
        $user = User::factory()->create(['role' => 'staff']);

        $this->actingAs($user)
            ->get(route('cases.create'))
            ->assertOk()
            ->assertSee('Select type')
            ->assertSee('Civil')
            ->assertSee('Criminal')
            ->assertSee('Collection');
    }
}
