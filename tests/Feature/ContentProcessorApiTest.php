<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Transcript;
use App\Models\User;
use App\Services\ContentProcessingProgressTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ContentProcessorApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ContentProcessingProgressTracker $progressTracker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user and authenticate
        $this->user = User::factory()->create();
        Passport::actingAs($this->user);
        
        // Get instance of progress tracker
        $this->progressTracker = app(ContentProcessingProgressTracker::class);
        
        // Disable job queue during tests
        Queue::fake();
    }

    /**
     * Test processing a URL
     */
    public function test_can_process_url(): void
    {
        $response = $this->postJson('/api/processor/process', [
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'content_id',
                        'source_type',
                        'source_url'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'Content processed successfully',
                    'data' => [
                        'source_type' => 'youtube',
                        'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
                    ]
                ]);
        
        $contentId = $response->json('data.content_id');
        $this->assertDatabaseHas('contents', [
            'id' => $contentId,
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
    }
    
    /**
     * Test validation of URL
     */
    public function test_url_validation(): void
    {
        $response = $this->postJson('/api/processor/process', [
            'url' => 'not-a-valid-url'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['url']);
    }
    
    /**
     * Test getting status of a content
     */
    public function test_can_get_content_status(): void
    {
        // Create test content
        $content = Content::factory()->create([
            'title' => 'Test Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        // Initialize progress tracking
        $this->progressTracker->startTracking($content);
        $this->progressTracker->updateProgress($content, 50, 'Processing video');
        
        $response = $this->getJson("/api/processor/status/{$content->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'content_id',
                        'source_type',
                        'source_url',
                        'title',
                        'status',
                        'progress',
                        'message'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'content_id' => $content->id,
                        'source_type' => 'youtube',
                        'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'title' => 'Test Video',
                        'status' => 'processing',
                        'progress' => 50,
                        'message' => 'Processing video'
                    ]
                ]);
    }
    
    /**
     * Test status with completed transcript
     */
    public function test_status_with_completed_transcript(): void
    {
        // Create test content with transcript
        $content = Content::factory()->create([
            'title' => 'Test Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        $transcript = Transcript::factory()->create([
            'content_id' => $content->id,
            'full_text' => 'This is a test transcript',
            'language' => 'en',
            'processed' => true,
            'token_count' => 100
        ]);
        
        $response = $this->getJson("/api/processor/status/{$content->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'content_id' => $content->id,
                        'source_type' => 'youtube',
                        'title' => 'Test Video',
                        'has_transcript' => true,
                        'transcript_id' => $transcript->id,
                        'processed' => true,
                    ]
                ]);
    }
    
    /**
     * Test status for non-existent content
     */
    public function test_status_for_nonexistent_content(): void
    {
        $response = $this->getJson("/api/processor/status/9999");
        
        $response->assertStatus(404);
    }
} 