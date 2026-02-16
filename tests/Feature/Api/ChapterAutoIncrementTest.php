<?php

namespace Tests\Feature\Api;

use App\Models\Sheet;
use App\Models\Subject;
use App\Models\User;
use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Cloudinary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery;
use Tests\TestCase;

class ChapterAutoIncrementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Cloudinary for all tests
        $uploadApiMock = Mockery::mock(UploadApi::class);
        $uploadApiMock->shouldReceive('upload')
            ->andReturnUsing(function ($file, $options) {
                $response = Mockery::mock(ApiResponse::class);
                $response->shouldReceive('offsetGet')->with('secure_url')->andReturn('https://example.com/test.pdf');
                $response->shouldReceive('offsetExists')->with('secure_url')->andReturn(true);

                return $response;
            });

        $cloudinaryMock = Mockery::mock(Cloudinary::class);
        $cloudinaryMock->shouldReceive('uploadApi')->andReturn($uploadApiMock);

        $this->app->instance(Cloudinary::class, $cloudinaryMock);
    }

    public function test_chapter_number_auto_increments_when_not_provided(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $subject = Subject::factory()->create(['code' => 'CS101']);

        // Upload first chapter without chapter_number
        $response1 = $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Chapter One',
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'file' => UploadedFile::fake()->create('c1.pdf', 100),
        ]);

        $response1->assertCreated();
        $this->assertEquals(1, $response1->json('data.chapter_number'));

        // Upload second chapter without chapter_number
        $response2 = $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Chapter Two',
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'file' => UploadedFile::fake()->create('c2.pdf', 100),
        ]);

        $response2->assertCreated();
        $this->assertEquals(2, $response2->json('data.chapter_number'));
    }

    public function test_chapter_number_can_be_provided_manually(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $subject = Subject::factory()->create(['code' => 'CS102']);

        // Upload chapter with manual chapter_number
        $response = $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Manual Chapter',
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 5,
            'file' => UploadedFile::fake()->create('c5.pdf', 100),
        ]);

        $response->assertCreated();
        $this->assertEquals(5, $response->json('data.chapter_number'));

        // Next auto-increment should be 6
        $response2 = $this->actingAs($user)->postJson('/api/sheets/upload', [
            'title' => 'Auto Chapter',
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'file' => UploadedFile::fake()->create('c6.pdf', 100),
        ]);

        $response2->assertCreated();
        $this->assertEquals(6, $response2->json('data.chapter_number'));
    }

    public function test_chapters_are_returned_ordered_by_number(): void
    {
        $subject = Subject::factory()->create(['code' => 'CS103']);

        // Create chapters out of order
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 3,
            'status' => 'approved',
        ]);
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 1,
            'status' => 'approved',
        ]);
        Sheet::factory()->create([
            'subject_id' => $subject->id,
            'type' => 'chapter',
            'chapter_number' => 2,
            'status' => 'approved',
        ]);

        $response = $this->getJson("/api/subjects/{$subject->code}");

        $response->assertOk();
        $this->assertEquals([1, 2, 3], $response->json('chapters'));
    }
}
