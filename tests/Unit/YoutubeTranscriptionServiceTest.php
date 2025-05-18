<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\ContentProcessorService;
use App\Services\SummaryGenerationService;
use App\Services\TranscriptionStrategyManager;
use App\Services\YoutubeTranscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class YoutubeTranscriptionServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected Content $content;
    protected ContentProcessorService $processorService;
    protected TranscriptionStrategyManager $strategyManager;
    protected SummaryGenerationService $summaryService;
    protected YoutubeTranscriptionService $transcriptionService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create real content for testing
        $this->content = Content::factory()->create([
            'title' => 'Test YouTube Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'
        ]);
        
        // Create mock services
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->processorService->shouldReceive('extractYoutubeId')
            ->with($this->content->source_url)
            ->andReturn('dQw4w9WgXcQ');
            
        $this->strategyManager = Mockery::mock(TranscriptionStrategyManager::class);
        $this->strategyManager->shouldReceive('setContentProcessor')->byDefault();
        $this->strategyManager->shouldReceive('addStrategy')->byDefault();
        
        $this->summaryService = Mockery::mock(SummaryGenerationService::class);
        
        // Create service with mock dependencies
        $this->transcriptionService = new YoutubeTranscriptionService(
            $this->processorService,
            $this->strategyManager,
            $this->summaryService
        );
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test processing a video with successful transcription and summary generation
     */
    public function test_process_video_with_successful_transcription(): void
    {
        // Create a transcript
        $transcript = new Transcript();
        $transcript->content_id = $this->content->id;
        $transcript->full_text = 'Test transcript content';
        $transcript->save();
        
        // Mock strategy manager to return the transcript
        $this->strategyManager->shouldReceive('processContent')
            ->once()
            ->with($this->content)
            ->andReturn($transcript);
            
        // Mock the summary service
        $this->summaryService->shouldReceive('generateSummary')
            ->once()
            ->with($this->content, $transcript)
            ->andReturn('This is a test summary');
        
        // Call the method
        $result = $this->transcriptionService->processVideo($this->content);
        
        // Assert the result
        $this->assertInstanceOf(Transcript::class, $result);
        $this->assertEquals('Test transcript content', $result->full_text);
        
        // Refresh content from database and check if summary was updated
        $this->content->refresh();
        $this->assertEquals('This is a test summary', $this->content->summary);
    }
    
    /**
     * Test that video processing handles invalid URLs
     */
    public function test_process_video_handles_invalid_url(): void
    {
        // Setup a mock that simulates an invalid YouTube URL
        $invalidContent = Content::factory()->create([
            'source_url' => 'https://invalid-url.com'
        ]);
        
        $mockProcessorService = Mockery::mock(ContentProcessorService::class);
        $mockProcessorService->shouldReceive('extractYoutubeId')
            ->with($invalidContent->source_url)
            ->andReturn(null);
            
        $mockStrategyManager = Mockery::mock(TranscriptionStrategyManager::class);
        $mockSummaryService = Mockery::mock(SummaryGenerationService::class);
        
        $service = new YoutubeTranscriptionService(
            $mockProcessorService,
            $mockStrategyManager,
            $mockSummaryService
        );
        
        // Process should return null for invalid URLs
        $result = $service->processVideo($invalidContent);
        
        $this->assertNull($result);
        
        // Clean up
        $invalidContent->delete();
    }
    
    /**
     * Test that processing works when no transcription strategy succeeds
     */
    public function test_process_video_handles_failed_transcription(): void
    {
        // Mock strategy manager to return null (no strategy succeeded)
        $this->strategyManager->shouldReceive('processContent')
            ->once()
            ->with($this->content)
            ->andReturn(null);
        
        // Summary service should not be called
        $this->summaryService->shouldNotReceive('generateSummary');
        
        // Call the method
        $result = $this->transcriptionService->processVideo($this->content);
        
        // Assert the result is null
        $this->assertNull($result);
    }
    
    /**
     * Test that generateAndUpdateSummary works with successful summary generation
     */
    public function test_generate_and_update_summary(): void
    {
        // Create a transcript
        $transcript = new Transcript();
        $transcript->content_id = $this->content->id;
        $transcript->full_text = 'Test transcript content for summary generation';
        $transcript->save();
        
        // Mock the summary service to return a summary
        $this->summaryService->shouldReceive('generateSummary')
            ->once()
            ->with($this->content, $transcript)
            ->andReturn('This is a generated summary');
        
        // Use reflection to access protected method
        $method = new \ReflectionMethod(YoutubeTranscriptionService::class, 'generateAndUpdateSummary');
        $method->setAccessible(true);
        
        // Call the method
        $method->invoke($this->transcriptionService, $this->content, $transcript);
        
        // Refresh content from database and check summary
        $this->content->refresh();
        $this->assertEquals('This is a generated summary', $this->content->summary);
    }
    
    /**
     * Test that updateContentFromVideoDetails updates the content title
     */
    public function test_update_content_from_video_details(): void
    {
        // Use the mock function to access commands/FetchVideoDetailsCommand
        $this->mock('App\Services\Commands\FetchVideoDetailsCommand', function ($mock) {
            $mock->shouldReceive('execute')
                ->with('dQw4w9WgXcQ', 'youtube')
                ->andReturn([
                    'title' => 'Rick Astley - Never Gonna Give You Up',
                    'duration' => 213,
                    'author' => 'Rick Astley'
                ]);
        });
        
        // Use reflection to access protected method
        $method = new \ReflectionMethod(YoutubeTranscriptionService::class, 'updateContentFromVideoDetails');
        $method->setAccessible(true);
        
        // Set content title to something else
        $this->content->title = 'Original Title';
        $this->content->save();
        
        // Call the method
        $method->invoke($this->transcriptionService, $this->content, 'dQw4w9WgXcQ');
        
        // Refresh content from database and check title
        $this->content->refresh();
        $this->assertEquals('Rick Astley - Never Gonna Give You Up', $this->content->title);
    }
} 