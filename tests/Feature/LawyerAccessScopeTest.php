<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\CaseAction;
use App\Models\Client;
use App\Models\Document;
use App\Models\Hearing;
use App\Models\Lawyer;
use App\Models\LegalCase;
use App\Models\OfficeExpense;
use App\Models\OpposingParty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_lawyer_dashboard_does_not_fall_back_to_admin_records_without_profile_link(): void
    {
        $user = User::factory()->create([
            'name' => 'Unlinked Lawyer',
            'email' => 'unlinked@example.com',
            'role' => 'lawyer',
        ]);
        $otherLawyer = Lawyer::create([
            'full_name' => 'Attorney Two',
            'email' => 'other-attorney@example.com',
            'status' => 'Active',
        ]);
        $otherClient = Client::create(['full_name' => 'Other Client', 'client_type' => 'Individual']);
        LegalCase::create([
            'case_number' => 'B-001',
            'case_title' => 'Other Matter',
            'client_id' => $otherClient->id,
            'assigned_lawyer_id' => $otherLawyer->id,
            'case_status' => 'Open',
            'priority_level' => 'High',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Dashboard')
            ->assertSee('My clients')
            ->assertSee('0')
            ->assertDontSee('Other Matter')
            ->assertDontSee('Other Client');
    }

    public function test_lawyer_account_can_match_lawyer_profile_by_name_or_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Alfred Verona',
            'email' => 'alfred@example.com',
            'role' => 'lawyer',
        ]);
        $lawyer = Lawyer::create([
            'full_name' => 'Alfred Verona',
            'email' => 'different-email@example.com',
            'status' => 'Active',
        ]);
        $client = Client::create(['full_name' => 'Assigned Client', 'client_type' => 'Individual']);
        LegalCase::create([
            'case_number' => 'A-001',
            'case_title' => 'Assigned Matter',
            'client_id' => $client->id,
            'assigned_lawyer_id' => $lawyer->id,
            'case_status' => 'Open',
            'priority_level' => 'High',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Assigned Matter')
            ->assertSee('Assigned Client');
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

    public function test_lawyer_cannot_create_or_edit_operational_records(): void
    {
        $user = User::factory()->create(['role' => 'lawyer']);
        $lawyer = Lawyer::create([
            'user_id' => $user->id,
            'full_name' => 'Attorney One',
            'email' => $user->email,
            'status' => 'Active',
        ]);
        $case = LegalCase::create([
            'case_number' => 'VIEW-ONLY-001',
            'case_title' => 'Assigned View Only Matter',
            'assigned_lawyer_id' => $lawyer->id,
        ]);
        $hearing = Hearing::create([
            'case_id' => $case->id,
            'hearing_date' => now()->addDay()->toDateString(),
        ]);
        $action = CaseAction::create([
            'case_id' => $case->id,
            'action_type' => 'Prepare pleading',
        ]);
        $party = OpposingParty::create([
            'case_id' => $case->id,
            'opposing_party_name' => 'Opposing Party',
        ]);
        $document = Document::create([
            'case_id' => $case->id,
            'document_name' => 'Assigned Document.pdf',
            'file_path' => 'documents/assigned-document.pdf',
        ]);

        foreach ([
            route('cases.edit', $case),
            route('hearings.create'),
            route('hearings.edit', $hearing),
            route('documents.create'),
            route('documents.edit', $document),
            route('case-actions.create'),
            route('case-actions.edit', $action),
            route('opposing-parties.create'),
            route('opposing-parties.edit', $party),
        ] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        $this->actingAs($user)
            ->get(route('cases.show', $case))
            ->assertOk()
            ->assertDontSee('Edit case');

        $this->actingAs($user)
            ->get(route('hearings.show', $hearing))
            ->assertOk()
            ->assertDontSee('Edit hearing');

        $this->actingAs($user)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertDontSee('Edit document');
    }

    public function test_private_document_download_is_limited_to_assigned_lawyer(): void
    {
        Storage::fake('local');

        $staff = User::factory()->create(['role' => 'staff']);
        $assignedUser = User::factory()->create(['role' => 'lawyer']);
        $otherUser = User::factory()->create(['role' => 'lawyer']);
        $assignedLawyer = Lawyer::create([
            'user_id' => $assignedUser->id,
            'full_name' => 'Assigned Attorney',
            'email' => $assignedUser->email,
        ]);
        Lawyer::create([
            'user_id' => $otherUser->id,
            'full_name' => 'Other Attorney',
            'email' => $otherUser->email,
        ]);
        $case = LegalCase::create([
            'case_number' => 'DOC-PRIVATE-001',
            'case_title' => 'Private Document Matter',
            'assigned_lawyer_id' => $assignedLawyer->id,
        ]);

        $this->actingAs($staff)
            ->post(route('documents.store'), [
                'case_id' => $case->id,
                'document_name' => 'Confidential Brief.pdf',
                'document_type' => 'Pleading',
                'file' => UploadedFile::fake()->create('brief.pdf', 50, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = Document::firstOrFail();

        Storage::disk('local')->assertExists($document->file_path);

        $this->actingAs($assignedUser)
            ->get(route('documents.download', $document))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('documents.download', $document))
            ->assertForbidden();
    }
}
