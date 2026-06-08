<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\LegalCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_welcome_page_shows_live_record_counts(): void
    {
        $client = Client::create([
            'full_name' => 'Welcome Page Client',
            'client_type' => 'Individual',
        ]);

        $activeCase = LegalCase::create([
            'case_number' => 'WELCOME-001',
            'case_title' => 'Active Welcome Case',
            'case_type' => 'Civil',
            'case_status' => 'Open',
            'client_id' => $client->id,
            'priority_level' => 'Medium',
        ]);

        LegalCase::create([
            'case_number' => 'WELCOME-002',
            'case_title' => 'Closed Welcome Case',
            'case_type' => 'Civil',
            'case_status' => 'Closed',
            'client_id' => $client->id,
            'priority_level' => 'Low',
        ]);

        Document::create([
            'case_id' => $activeCase->id,
            'document_name' => 'Welcome Document',
            'document_type' => 'Pleading',
            'file_path' => 'documents/welcome-document.pdf',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder([
                '1',
                'Client records',
                '1',
                'Active matters',
                '1',
                'Filed documents',
            ]);
    }
}
