<?php
// [ai-generated-code]

namespace Tests\Unit\Services;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\ContentProcessorService;
use App\Services\TranscriptionService;
use App\Services\TranscriptionStrategyManager;
use App\Services\TranscriptionStrategies\WhisperTranscriptionStrategy;
use App\Services\TranscriptionStrategies\YoutubeSubtitlesStrategy;
use App\Services\TranscriptionStrategies\VimeoStrategy;
use App\Services\Commands\DownloadAudioCommand;
use App\Services\Commands\WhisperTranscriptionCommand;
use App\Services\Commands\FetchVideoDetailsCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class TranscriptionServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected TranscriptionService $service;
    protected ContentProcessorService $contentProcessor;
    protected TranscriptionStrategyManager $strategyManager;
    protected WhisperTranscriptionStrategy $whisperStrategy;
    protected YoutubeSubtitlesStrategy $youtubeStrategy;
    protected VimeoStrategy $vimeoStrategy;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create mocks
        $this->contentProcessor = Mockery::mock(ContentProcessorService::class);
        $this->strategyManager = Mockery::mock(TranscriptionStrategyManager::class);
        $this->whisperStrategy = Mockery::mock(WhisperTranscriptionStrategy::class);
        $this->youtubeStrategy = Mockery::mock(YoutubeSubtitlesStrategy::class);
        $this->vimeoStrategy = Mockery::mock(VimeoStrategy::class);
        
        // Setup service with mocks
        $this->service = new TranscriptionService(
            $this->contentProcessor,
            $this->strategyManager
        );
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    public function testProcessMediaWithYoutubeUrl(): void
    {
        // Create test content
        $content = new Content();
        $content->source_url = 'https://www.youtube.com/watch?v=abc123';
        $content->save();
        
        $transcript = new Transcript();
        $transcript->content_id = $content->id;
        $transcript->full_text = 'Test transcript';
        
        // Set up expectations
        $this->contentProcessor->shouldReceive('extractYoutubeId')
            ->once()
            ->with($content->source_url)
            ->andReturn('abc123');
        
        $this->strategyManager->shouldReceive('setContentProcessor')
            ->once()
            ->with($this->contentProcessor);
        
        $this->strategyManager->shouldReceive('addStrategy')
            ->times(3);
        
        $this->strategyManager->shouldReceive('processContent')
            ->once()
            ->with($content)
            ->andReturn($transcript);
        
        // Call the method
        $result = $this->service->processMedia($content);
        
        // Assert the result
        $this->assertInstanceOf(Transcript::class, $result);
        $this->assertEquals('Test transcript', $result->full_text);
    }
    
    public function testProcessMediaWithVimeoUrl(): void
    {
        // Create test content
        $content = new Content();
        $content->source_url = 'https://vimeo.com/123456789';
        $content->save();
        
        $transcript = new Transcript();
        $transcript->content_id = $content->id;
        $transcript->full_text = 'Vimeo test transcript';
        
        // Set up expectations
        $this->contentProcessor->shouldReceive('extractYoutubeId')
            ->once()
            ->with($content->source_url)
            ->andReturn(null); // Not a YouTube URL
        
        $this->strategyManager->shouldReceive('setContentProcessor')
            ->once()
            ->with($this->contentProcessor);
        
        $this->strategyManager->shouldReceive('addStrategy')
            ->times(3);
        
        $this->strategyManager->shouldReceive('processContent')
            ->once()
            ->with($content)
            ->andReturn($transcript);
        
        // Call the method
        $result = $this->service->processMedia($content);
        
        // Assert the result
        $this->assertInstanceOf(Transcript::class, $result);
        $this->assertEquals('Vimeo test transcript', $result->full_text);
    }
    
    public function testProcessMediaWithInvalidUrl(): void
    {
        // Create test content
        $content = new Content();
        $content->source_url = 'https://example.com/video'; // Invalid URL for our service
        $content->save();
        
        // Set up expectations
        $this->contentProcessor->shouldReceive('extractYoutubeId')
            ->once()
            ->with($content->source_url)
            ->andReturn(null);
        
        $this->strategyManager->shouldReceive('setContentProcessor')
            ->once()
            ->with($this->contentProcessor);
        
        $this->strategyManager->shouldReceive('addStrategy')
            ->times(3);
        
        // Call the method - should return null due to invalid URL
        $result = $this->service->processMedia($content);
        
        // Assert the result
        $this->assertNull($result);
    }
} 