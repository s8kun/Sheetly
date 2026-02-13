<?php

namespace Tests\Feature\Api;

use App\Models\Sheet;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_subjects(): void
    {
        Subject::factory()->create(['name' => 'Software Engineering', 'code' => 'SE311']);
        Subject::factory()->create(['name' => 'Database Systems', 'code' => 'CS322']);

        $response = $this->getJson('/api/subjects?search=SE311');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['code' => 'SE311']);
    }

    public function test_can_view_subject_details_with_chapters_and_exams(): void
    {
        $subject = Subject::factory()->create(['code' => 'SE311']);
        
        // Create 2 chapters
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 1,
            'status' => 'approved'
        ]);
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 2,
            'status' => 'approved'
        ]);

        // Create 1 midterm
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'midterm',
            'status' => 'approved',
            'title' => 'Midterm 2024'
        ]);

        $response = $this->getJson("/api/subjects/{$subject->code}");

        $response->assertOk()
            ->assertJsonStructure([
                'subject',
                'chapters',
                'midterms',
                'finals'
            ])
            ->assertJsonCount(2, 'chapters')
            ->assertJsonCount(1, 'midterms')
            ->assertJsonCount(0, 'finals'); // We didn't create a final in the setup
    }

    public function test_registration_validation(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@gmail.com', // Invalid domain
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@uob.edu.ly', // Valid domain
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['access_token', 'token_type']);
    }

    public function test_sheet_upload_and_approval_workflow(): void
    {
        // 1. User uploads a sheet (Simulated by factory to avoid Cloudinary mocking complexity here)
        $user = User::factory()->create(['role' => 'student']);
        $subject = Subject::factory()->create();

        $sheet = Sheet::factory()->create([
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'title' => 'Test Pending Sheet',
            'status' => 'pending',
            'type' => 'chapter',
            'chapter_number' => 1,
            'file_url' => 'https://example.com/test.pdf'
        ]);

        $sheetId = $sheet->id;

        // 2. User sees it in "My Sheets" as pending
        $this->actingAs($user)
             ->getJson('/api/my-sheets')
             ->assertOk()
             ->assertJsonFragment(['title' => 'Test Pending Sheet', 'status' => 'pending']);

        // 3. Other users (or public) CANNOT see it yet (chapters should be empty or not contain 1)
        $this->getJson("/api/subjects/{$subject->code}")
             ->assertOk()
             ->assertJsonMissing([1]); 

        // 4. Admin sees it in "Pending Sheets"
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
             ->getJson('/api/admin/sheets/pending')
             ->assertOk()
             ->assertJsonFragment(['title' => 'Test Pending Sheet']);

        // 5. Admin approves it
        $this->actingAs($admin)
             ->patchJson("/api/admin/sheets/{$sheetId}/approve")
             ->assertOk();

        // 6. User sees it as approved
        $this->actingAs($user)
             ->getJson('/api/my-sheets')
             ->assertOk()
             ->assertJsonFragment(['title' => 'Test Pending Sheet', 'status' => 'approved']);

        // 7. Now it is visible publicly (chapter 1 is now in the list)
        $this->getJson("/api/subjects/{$subject->code}")
             ->assertOk()
             ->assertJsonFragment([1]);
    }

    public function test_sheet_upload_has_correct_folder_structure(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $subject = Subject::factory()->create(['code' => 'TEST101']);

        // Mock UploadApi
        $uploadApiMock = \Mockery::mock(\Cloudinary\Api\Upload\UploadApi::class);
        
        // Helper to create a mock ApiResponse that supports array access for secure_url
        $createResponse = function($url) {
            $response = \Mockery::mock(\Cloudinary\Api\ApiResponse::class);
            $response->shouldReceive('offsetGet')->with('secure_url')->andReturn($url);
            $response->shouldReceive('offsetExists')->with('secure_url')->andReturn(true);
            return $response;
        };

        // 1. Chapter Upload Expectation
        $uploadApiMock->shouldReceive('upload')
            ->once()
            ->with(\Mockery::any(), \Mockery::on(function ($options) {
                return $options['folder'] === 'Sheetly/TEST101/Chapters/Chapter-5';
            }))
            ->andReturn($createResponse('https://example.com/chapter.pdf'));

        // 2. Midterm Upload Expectation
        $uploadApiMock->shouldReceive('upload')
            ->once()
            ->with(\Mockery::any(), \Mockery::on(function ($options) {
                return $options['folder'] === 'Sheetly/TEST101/Midterms';
            }))
            ->andReturn($createResponse('https://example.com/midterm.pdf'));

        // 3. Final Upload Expectation
        $uploadApiMock->shouldReceive('upload')
            ->once()
            ->with(\Mockery::any(), \Mockery::on(function ($options) {
                return $options['folder'] === 'Sheetly/TEST101/Finals';
            }))
            ->andReturn($createResponse('https://example.com/final.pdf'));

        // 4. Midterm with Number Upload Expectation (New Logic)
        $uploadApiMock->shouldReceive('upload')
            ->once()
            ->with(\Mockery::any(), \Mockery::on(function ($options) {
                return $options['folder'] === 'Sheetly/TEST101/Midterms/Midterm-1';
            }))
            ->andReturn($createResponse('https://example.com/midterm-1.pdf'));

        $cloudinaryMock = \Mockery::mock(\Cloudinary\Cloudinary::class);
        $cloudinaryMock->shouldReceive('uploadApi')->andReturn($uploadApiMock);

        // Bind mock to container
        $this->app->instance(\Cloudinary\Cloudinary::class, $cloudinaryMock);

        // 1. Chapter Upload
        $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Chapter 5',
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 5,
            'file' => \Illuminate\Http\UploadedFile::fake()->create('chapter.pdf', 100)
        ])->assertCreated();

        // 2. Midterm Upload (No Number)
        $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Midterm Exam',
            'subject_id' => $subject->id,
            'type' => 'midterm',
            'file' => \Illuminate\Http\UploadedFile::fake()->create('midterm.pdf', 100)
        ])->assertCreated();

        // 3. Final Upload (No Number)
        $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Final Exam',
            'subject_id' => $subject->id,
            'type' => 'final',
            'file' => \Illuminate\Http\UploadedFile::fake()->create('final.pdf', 100)
        ])->assertCreated();

        // 4. Midterm with Number Upload
        $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'First Midterm',
            'subject_id' => $subject->id,
            'type' => 'midterm',
            'chapter_number' => 1,
            'file' => \Illuminate\Http\UploadedFile::fake()->create('midterm1.pdf', 100)
        ])->assertCreated();
    }
}
