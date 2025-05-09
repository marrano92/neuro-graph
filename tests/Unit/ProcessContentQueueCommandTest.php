<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Console\Commands\ProcessContentQueue;
use App\Jobs\ProcessContentJob;
use App\Models\Content;
use App\Models\Transcript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessContentQueueCommandTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable real queue
        Queue::fake();
    }
    
    /**
     * Test processing a specific content item
     */
    public function test_process_specific_content(): void
    {
        // Create test content
        $content = Content::factory()->create([
            'title' => 'Test Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        // Run command
        $this->artisan('content:process', ['id' => $content->id])
            ->expectsOutput("Dispatching job to process content: {$content->id}")
            ->expectsOutput('Job dispatched successfully')
            ->assertExitCode(0);
        
        // Assert job was queued with the right queue name
        Queue::assertPushedOn('content-processing', ProcessContentJob::class);
        
        // Assert at least one job was pushed
        $this->assertGreaterThan(0, Queue::pushed(ProcessContentJob::class)->count());
    }
    
    /**
     * Test error on non-existent content
     */
    public function test_error_on_nonexistent_content(): void
    {
        $nonExistentId = 9999;
        
        $this->artisan('content:process', ['id' => $nonExistentId])
            ->expectsOutput("Content with ID {$nonExistentId} not found")
            ->assertExitCode(1);
        
        // No jobs should be queued
        Queue::assertNothingPushed();
    }
    
    /**
     * Test processing all unprocessed content
     */
    public function test_process_all_unprocessed(): void
    {
        // Create processed and unprocessed content
        $processedContent = Content::factory()->create(['source_type' => 'youtube']);
        Transcript::factory()->create([
            'content_id' => $processedContent->id,
            'processed' => true
        ]);
        
        $unprocessedContent1 = Content::factory()->create(['source_type' => 'youtube']);
        $unprocessedContent2 = Content::factory()->create(['source_type' => 'article']);
        
        // Run command with --all flag
        $this->artisan('content:process', ['--all' => true])
            ->expectsOutput("Found 2 unprocessed content items")
            ->assertExitCode(0);
        
        // Assert jobs were queued
        Queue::assertPushedOn('content-processing', ProcessContentJob::class);
        
        // Check that exactly 2 jobs were pushed (for the 2 unprocessed contents)
        $this->assertEquals(2, Queue::pushed(ProcessContentJob::class)->count());
    }
    
    /**
     * Test no unprocessed content scenario
     */
    public function test_no_unprocessed_content(): void
    {
        // Create only processed content
        $processedContent = Content::factory()->create(['source_type' => 'youtube']);
        Transcript::factory()->create([
            'content_id' => $processedContent->id,
            'processed' => true
        ]);
        
        // Run command with --all flag
        $this->artisan('content:process', ['--all' => true])
            ->expectsOutput("No unprocessed content found")
            ->assertExitCode(0);
        
        // No jobs should be queued
        Queue::assertNothingPushed();
    }
    
    /**
     * Test missing required parameters
     */
    public function test_missing_required_parameters(): void
    {
        $this->artisan('content:process')
            ->expectsOutput('Please provide a content ID or use --all option')
            ->assertExitCode(1);
        
        // No jobs should be queued
        Queue::assertNothingPushed();
    }
} 