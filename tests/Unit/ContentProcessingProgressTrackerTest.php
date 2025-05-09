<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Services\ContentProcessingProgressTracker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentProcessingProgressTrackerTest extends TestCase
{
    protected ContentProcessingProgressTracker $progressTracker;
    protected Content $content;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->progressTracker = new ContentProcessingProgressTracker();
        
        // Create a test content
        $this->content = Content::factory()->create([
            'title' => 'Test Content',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
    }

    protected function tearDown(): void
    {
        // Clean up after tests
        $this->content->delete();
        Cache::forget("content_processing_progress:{$this->content->id}");
        
        parent::tearDown();
    }

    /**
     * Test that progress tracking can be started
     */
    public function test_can_start_tracking_progress(): void
    {
        $this->progressTracker->startTracking($this->content);
        
        $cacheKey = "content_processing_progress:{$this->content->id}";
        $this->assertTrue(Cache::has($cacheKey));
        
        $progress = Cache::get($cacheKey);
        $this->assertEquals(0, $progress['current_step']);
        $this->assertEquals(100, $progress['total_steps']);
        $this->assertEquals('processing', $progress['status']);
    }

    /**
     * Test that progress can be updated
     */
    public function test_can_update_progress(): void
    {
        $this->progressTracker->startTracking($this->content);
        
        $this->progressTracker->updateProgress($this->content, 50, 'Halfway there');
        
        $progress = $this->progressTracker->getProgress($this->content);
        $this->assertEquals(50, $progress['current_step']);
        $this->assertEquals('Halfway there', $progress['message']);
    }

    /**
     * Test that progress percentage is calculated correctly
     */
    public function test_calculates_progress_percentage(): void
    {
        $this->progressTracker->startTracking($this->content, 200);
        $this->progressTracker->updateProgress($this->content, 50);
        
        $percentage = $this->progressTracker->getProgressPercentage($this->content);
        $this->assertEquals(25, $percentage); // 50/200 = 25%
    }

    /**
     * Test that progress can be marked as completed
     */
    public function test_can_complete_tracking(): void
    {
        $this->progressTracker->startTracking($this->content);
        $this->progressTracker->completeTracking($this->content, 'All done');
        
        $progress = $this->progressTracker->getProgress($this->content);
        $this->assertEquals('completed', $progress['status']);
        $this->assertEquals(100, $progress['current_step']);
        $this->assertEquals('All done', $progress['message']);
        $this->assertTrue(isset($progress['completed_at']));
    }

    /**
     * Test that progress can be marked as failed
     */
    public function test_can_fail_tracking(): void
    {
        $this->progressTracker->startTracking($this->content);
        $this->progressTracker->failTracking($this->content, 'Something went wrong');
        
        $progress = $this->progressTracker->getProgress($this->content);
        $this->assertEquals('failed', $progress['status']);
        $this->assertEquals('Something went wrong', $progress['message']);
        $this->assertTrue(isset($progress['failed_at']));
    }

    /**
     * Test that isCompleted returns correct value
     */
    public function test_is_completed_returns_correct_value(): void
    {
        $this->progressTracker->startTracking($this->content);
        $this->assertFalse($this->progressTracker->isCompleted($this->content));
        
        $this->progressTracker->completeTracking($this->content);
        $this->assertTrue($this->progressTracker->isCompleted($this->content));
    }

    /**
     * Test that hasFailed returns correct value
     */
    public function test_has_failed_returns_correct_value(): void
    {
        $this->progressTracker->startTracking($this->content);
        $this->assertFalse($this->progressTracker->hasFailed($this->content));
        
        $this->progressTracker->failTracking($this->content);
        $this->assertTrue($this->progressTracker->hasFailed($this->content));
    }
} 