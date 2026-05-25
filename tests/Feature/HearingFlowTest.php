<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HearingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_hearings_index_shows_case_client_lawyer_and_schedule(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['full_name' => 'Ralph Medino']);
        $lawyer = Lawyer::create(['full_name' => 'Atty. Maria Santos']);
        $case = LegalCase::create([
            'case_number' => 'HEAR-001',
            'case_title' => 'Criminal Case',
            'client_id' => $client->id,
            'assigned_lawyer_id' => $lawyer->id,
        ]);
        Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => '2026-06-20',
            'hearing_time' => '08:30:00',
            'court_venue' => 'Supreme Court, Manila',
            'hearing_status' => 'Scheduled',
        ]);

        $this->actingAs($user)
            ->get(route('hearings.index'))
            ->assertOk()
            ->assertSee('Jun 20, 2026')
            ->assertSee('Criminal Case')
            ->assertSee('Ralph Medino')
            ->assertSee('Atty. Maria Santos')
            ->assertSee('Supreme Court, Manila');
    }

    public function test_hearing_detail_shows_context_and_open_case_link(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['full_name' => 'Ralph Medino']);
        $lawyer = Lawyer::create(['full_name' => 'Atty. Maria Santos']);
        $case = LegalCase::create([
            'case_number' => 'HEAR-002',
            'case_title' => 'Civil Case',
            'client_id' => $client->id,
            'assigned_lawyer_id' => $lawyer->id,
        ]);
        $hearing = Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => '2026-06-21',
            'hearing_time' => '09:00:00',
            'court_venue' => 'RTC Branch 8',
            'judge_name' => 'Judge Cruz',
            'hearing_purpose' => 'Pre-trial',
        ]);

        $this->actingAs($user)
            ->get(route('hearings.show', $hearing))
            ->assertOk()
            ->assertSee('Civil Case')
            ->assertSee('Ralph Medino')
            ->assertSee('Atty. Maria Santos')
            ->assertSee('Judge Cruz')
            ->assertSee('Open case');
    }

    public function test_hearing_forms_include_jurisdiction_and_branch_dropdowns(): void
    {
        $user = User::factory()->create();
        $case = LegalCase::create([
            'case_number' => 'HEAR-003',
            'case_title' => 'Jurisdiction Matter',
        ]);

        $this->actingAs($user)
            ->get(route('hearings.create'))
            ->assertOk()
            ->assertSee('Jurisdiction')
            ->assertSee('Cybercrime')
            ->assertSee('RTC Branch 8 - Tacloban City, Leyte');

        $this->actingAs($user)
            ->post(route('hearings.store'), [
                'case_id' => $case->id,
                'hearing_date' => '2026-06-22',
                'court_jurisdiction' => 'Cybercrime',
                'court_branch' => 'RTC Branch 8 - Tacloban City, Leyte',
            ])
            ->assertRedirect();

        $hearing = Hearing::firstOrFail();
        $this->assertSame('Cybercrime', $hearing->court_jurisdiction);
        $this->assertSame('RTC Branch 8 - Tacloban City, Leyte', $hearing->court_branch);
    }
}
