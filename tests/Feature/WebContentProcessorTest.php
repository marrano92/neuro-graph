<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Transcript;
use App\Models\User;
use App\Services\ContentProcessingProgressTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class WebContentProcessorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected ContentProcessingProgressTracker $progressTracker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Get instance of progress tracker
        $this->progressTracker = app(ContentProcessingProgressTracker::class);
        
        // Disable job queue during tests
        Queue::fake();
    }

    /**
     * Test content processor form is accessible
     */
    public function test_processor_form_is_accessible(): void
    {
        $response = $this->actingAs($this->user)
                        ->get(route('content.processor'));
        
        $response->assertStatus(200)
                ->assertViewIs('content-processor.index');
    }
    
    /**
     * Test submitting URL for processing
     */
    public function test_can_submit_url_for_processing(): void
    {
        $response = $this->actingAs($this->user)
                        ->post(route('content.processor.process'), [
                            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
                        ]);
        
        $response->assertStatus(302)
                ->assertRedirect();
        
        $this->assertDatabaseHas('contents', [
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
    }
    
    /**
     * Test URL validation
     */
    public function test_url_validation(): void
    {
        $response = $this->actingAs($this->user)
                        ->from(route('content.processor'))
                        ->post(route('content.processor.process'), [
                            'url' => 'not-a-valid-url'
                        ]);
        
        $response->assertStatus(302)
                ->assertRedirect(route('content.processor'))
                ->assertSessionHasErrors('url');
    }
    
    /**
     * Test view status page
     */
    public function test_can_view_status_page(): void
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
        
        $response = $this->actingAs($this->user)
                        ->get(route('content.processor.status', ['content' => $content->id]));
        
        $response->assertStatus(200)
                ->assertViewIs('content-processor.status')
                ->assertViewHas('content', $content)
                ->assertViewHas('percentage', 50)
                ->assertSee('Processing video');
    }
    
    /**
     * Test status page with completed transcript
     */
    public function test_status_page_with_completed_transcript(): void
    {
        // Create test content with transcript
        $content = Content::factory()->create([
            'title' => 'Test Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        Transcript::factory()->create([
            'content_id' => $content->id,
            'full_text' => 'This is a test transcript',
            'language' => 'en',
            'processed' => true
        ]);
        
        $this->progressTracker->startTracking($content);
        $this->progressTracker->completeTracking($content, 'Processing completed');
        
        $response = $this->actingAs($this->user)
                        ->get(route('content.processor.status', ['content' => $content->id]));
        
        $response->assertStatus(200)
                ->assertViewHas('percentage', 100)
                ->assertSee('Processing completed');
    }
    
    /**
     * Test content list page
     */
    public function test_can_view_contents_list(): void
    {
        // Create multiple test contents
        $content1 = Content::factory()->create([
            'title' => 'Test Video 1',
            'source_type' => 'youtube'
        ]);
        
        $content2 = Content::factory()->create([
            'title' => 'Test Article 1',
            'source_type' => 'article'
        ]);
        
        $response = $this->actingAs($this->user)
                        ->get(route('content.processor.list'));
        
        $response->assertStatus(200)
                ->assertViewIs('content-processor.list')
                ->assertViewHas('contents')
                ->assertSee('Test Video 1')
                ->assertSee('Test Article 1');
    }
    
    /**
     * Test guest users cannot access processor
     */
    public function test_guest_users_cannot_access_processor(): void
    {
        $response = $this->get(route('content.processor'));
        
        $response->assertStatus(302)
                ->assertRedirect();
    }
} 