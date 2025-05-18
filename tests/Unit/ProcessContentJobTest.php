<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use App\Models\Transcript;
use App\Services\ArticleProcessorService;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use App\Services\YoutubeTranscriptionService;
use App\Services\SummaryGenerationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use Tests\TestCase;

class ProcessContentJobTest extends TestCase
{
    use RefreshDatabase;
    
    protected Content $youtubeContent;
    protected Content $articleContent;
    protected Content $unknownContent;
    protected ContentProcessorService $processorService;
    protected YoutubeTranscriptionService $youtubeService;
    protected ArticleProcessorService $articleService;
    protected ContentProcessingProgressTracker $progressTracker;
    protected SummaryGenerationService $summaryService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create mocks
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->youtubeService = Mockery::mock(YoutubeTranscriptionService::class);
        $this->articleService = Mockery::mock(ArticleProcessorService::class);
        $this->progressTracker = Mockery::mock(ContentProcessingProgressTracker::class);
        $this->summaryService = Mockery::mock(SummaryGenerationService::class);
        
        // Create test content items
        $this->youtubeContent = Content::factory()->create([
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=test123'
        ]);
        
        $this->articleContent = Content::factory()->create([
            'source_type' => 'article',
            'source_url' => 'https://example.com/test-article'
        ]);
        
        $this->unknownContent = Content::factory()->create([
            'source_type' => 'unknown',
            'source_url' => 'https://example.com/unknown'
        ]);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test job processes YouTube content successfully
     */
    public function test_processes_youtube_content(): void
    {
        // Create fresh mocks for each test
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->youtubeService = Mockery::mock(YoutubeTranscriptionService::class);
        $this->articleService = Mockery::mock(ArticleProcessorService::class);
        $this->progressTracker = Mockery::mock(ContentProcessingProgressTracker::class);
        
        // Create a mock transcript
        $transcript = Mockery::mock(Transcript::class);
        $transcript->shouldReceive('getAttribute')->with('id')->andReturn(1);
        
        // Setup progress tracker expectations - use zeroOrMoreTimes() to be flexible
        $this->progressTracker->shouldReceive('startTracking')
            ->zeroOrMoreTimes();
        
        $this->progressTracker->shouldReceive('updateProgress')
            ->zeroOrMoreTimes();
        
        $this->progressTracker->shouldReceive('completeTracking')
            ->zeroOrMoreTimes();
        
        // Setup YouTube service expectations
        $this->youtubeService->shouldReceive('processVideo')
            ->once()
            ->with($this->youtubeContent)
            ->andReturn($transcript);
        
        // Create and handle the job
        $job = new ProcessContentJob($this->youtubeContent);
        $job->handle(
            $this->processorService,
            $this->youtubeService,
            $this->articleService,
            $this->progressTracker
        );
        
        // Basic assertion that test completes without exceptions
        $this->assertTrue(true);
    }
    
    /**
     * Test job processes article content successfully
     */
    public function test_processes_article_content(): void
    {
        // Create fresh mocks for each test
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->youtubeService = Mockery::mock(YoutubeTranscriptionService::class);
        $this->articleService = Mockery::mock(ArticleProcessorService::class);
        $this->progressTracker = Mockery::mock(ContentProcessingProgressTracker::class);
        
        // Create a mock transcript
        $transcript = Mockery::mock(Transcript::class);
        $transcript->shouldReceive('getAttribute')->with('id')->andReturn(2);
        
        // Setup progress tracker expectations - use zeroOrMoreTimes() to be flexible
        $this->progressTracker->shouldReceive('startTracking')
            ->zeroOrMoreTimes();
        
        $this->progressTracker->shouldReceive('updateProgress')
            ->zeroOrMoreTimes();
        
        $this->progressTracker->shouldReceive('completeTracking')
            ->zeroOrMoreTimes();
        
        // Setup article service expectations
        $this->articleService->shouldReceive('processArticle')
            ->once()
            ->with($this->articleContent)
            ->andReturn($transcript);
        
        // Create and handle the job
        $job = new ProcessContentJob($this->articleContent);
        $job->handle(
            $this->processorService,
            $this->youtubeService,
            $this->articleService,
            $this->progressTracker
        );
        
        // Basic assertion that test completes without exceptions
        $this->assertTrue(true);
    }
    
    /**
     * Test job handles YouTube processing failure
     */
    public function test_handles_youtube_processing_failure(): void
    {
        // Create fresh mocks for each test
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->youtubeService = Mockery::mock(YoutubeTranscriptionService::class);
        $this->articleService = Mockery::mock(ArticleProcessorService::class);
        $this->progressTracker = Mockery::mock(ContentProcessingProgressTracker::class);
        
        // Setup progress tracker expectations - use zeroOrMoreTimes() to be flexible
        $this->progressTracker->shouldReceive('startTracking')->zeroOrMoreTimes();
        $this->progressTracker->shouldReceive('updateProgress')->zeroOrMoreTimes();
        $this->progressTracker->shouldReceive('failTracking')->zeroOrMoreTimes();
        
        // Setup YouTube service to return null (failure)
        $this->youtubeService->shouldReceive('processVideo')
            ->once()
            ->with($this->youtubeContent)
            ->andReturn(null);
        
        // Create the job
        $job = new ProcessContentJob($this->youtubeContent);
        
        // Expect exception to be thrown
        $this->expectException(Exception::class);
        
        // Handle the job (should throw exception)
        $job->handle(
            $this->processorService,
            $this->youtubeService,
            $this->articleService,
            $this->progressTracker
        );
    }
    
    /**
     * Test job handles unsupported content type
     */
    public function test_handles_unsupported_content_type(): void
    {
        // Create fresh mocks for each test
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->youtubeService = Mockery::mock(YoutubeTranscriptionService::class);
        $this->articleService = Mockery::mock(ArticleProcessorService::class);
        $this->progressTracker = Mockery::mock(ContentProcessingProgressTracker::class);
        
        // Create unsupported content type
        $unsupportedContent = Content::factory()->create([
            'source_type' => 'unsupported',
            'source_url' => 'https://example.com/unsupported'
        ]);
        
        // Setup progress tracker expectations
        $this->progressTracker->shouldReceive('startTracking')->zeroOrMoreTimes();
        $this->progressTracker->shouldReceive('updateProgress')->zeroOrMoreTimes();
        $this->progressTracker->shouldReceive('failTracking')->zeroOrMoreTimes();
        
        // Create the job
        $job = new ProcessContentJob($unsupportedContent);
        
        // Expect exception to be thrown
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Unsupported content type: unsupported");
        
        // Handle the job (should throw exception)
        $job->handle(
            $this->processorService,
            $this->youtubeService,
            $this->articleService,
            $this->progressTracker
        );
        
        // Clean up
        $unsupportedContent->delete();
    }
} 