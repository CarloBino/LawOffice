<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LawyerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_lawyers_index_shows_workload_summary(): void
    {
        $user = User::factory()->create();
        $lawyer = Lawyer::create([
            'full_name' => 'Atty. Maria Santos',
            'email' => 'maria@example.com',
            'specialization' => 'Criminal Law',
            'status' => 'Active',
        ]);
        LegalCase::create([
            'case_number' => 'LAW-001',
            'case_title' => 'Assigned Matter',
            'assigned_lawyer_id' => $lawyer->id,
            'case_status' => 'Active',
            'priority_level' => 'High',
        ]);

        $this->actingAs($user)
            ->get(route('lawyers.index'))
            ->assertOk()
            ->assertSee('Atty. Maria Santos')
            ->assertSee('Criminal Law')
            ->assertSee('1')
            ->assertSee('Manage');
    }

    public function test_lawyer_profile_shows_assigned_cases_and_hearings(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['full_name' => 'Ralph Medino']);
        $lawyer = Lawyer::create([
            'full_name' => 'Atty. Maria Santos',
            'specialization' => 'Criminal Law',
            'status' => 'Active',
        ]);
        $case = LegalCase::create([
            'case_number' => 'LAW-002',
            'case_title' => 'Criminal Matter',
            'client_id' => $client->id,
            'assigned_lawyer_id' => $lawyer->id,
            'case_status' => 'Active',
            'priority_level' => 'High',
        ]);
        Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => '2026-06-15',
            'hearing_time' => '09:30:00',
            'court_venue' => 'RTC Branch 8',
            'hearing_purpose' => 'Pre-trial',
        ]);

        $this->actingAs($user)
            ->get(route('lawyers.show', $lawyer))
            ->assertOk()
            ->assertSee('Lawyer profile')
            ->assertSee('LAW-002')
            ->assertSee('Criminal Matter')
            ->assertSee('Ralph Medino')
            ->assertSee('RTC Branch 8');
    }
}
