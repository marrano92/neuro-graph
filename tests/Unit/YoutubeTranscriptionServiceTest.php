<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\ContentProcessorService;
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
        
        // Create mock processor service
        $this->processorService = Mockery::mock(ContentProcessorService::class);
        $this->processorService->shouldReceive('extractYoutubeId')
            ->with($this->content->source_url)
            ->andReturn('dQw4w9WgXcQ');
        
        // Create service with mock dependencies
        $this->transcriptionService = new YoutubeTranscriptionService($this->processorService);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test that we can create a transcript from text
     * 
     * This tests the basic functionality of creating transcript records
     * without actually calling external APIs
     */
    public function test_can_create_transcript_from_text(): void
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(YoutubeTranscriptionService::class, 'createTranscriptFromText');
        $method->setAccessible(true);
        
        $sampleText = <<<EOT
This is a test transcript.
It has multiple paragraphs.

This is the second paragraph.
It should be chunked properly.

And here's a third paragraph for good measure.
EOT;
        
        $transcript = $method->invoke($this->transcriptionService, $this->content, $sampleText);
        
        // Assert transcript was created correctly
        $this->assertInstanceOf(Transcript::class, $transcript);
        $this->assertEquals($this->content->id, $transcript->content_id);
        $this->assertEquals($sampleText, $transcript->full_text);
        $this->assertEquals('en', $transcript->language);
        $this->assertEquals($this->content->source_url, $transcript->source_url);
        $this->assertTrue($transcript->processed);
        
        // Assert chunks were created
        $this->assertGreaterThan(0, $transcript->chunks()->count());
        
        // Clean up
        $transcript->chunks()->delete();
        $transcript->delete();
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
        
        $service = new YoutubeTranscriptionService($mockProcessorService);
        
        // Process should return null for invalid URLs
        $result = $service->processVideo($invalidContent);
        
        $this->assertNull($result);
        
        // Clean up
        $invalidContent->delete();
    }
    
    /**
     * Test that the chunking functionality works properly
     */
    public function test_text_chunking(): void
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(YoutubeTranscriptionService::class, 'chunkText');
        $method->setAccessible(true);
        
        $sampleText = <<<EOT
Paragraph one.

Paragraph two.

Paragraph three.
EOT;
        
        $chunks = $method->invoke($this->transcriptionService, $sampleText);
        
        $this->assertCount(3, $chunks);
        $this->assertEquals('Paragraph one.', $chunks[0]['text']);
        $this->assertEquals('Paragraph two.', $chunks[1]['text']);
        $this->assertEquals('Paragraph three.', $chunks[2]['text']);
    }
    
    /**
     * Test that token counting works approximately as expected
     */
    public function test_token_counting(): void
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(YoutubeTranscriptionService::class, 'countTokens');
        $method->setAccessible(true);
        
        $text = "This is a test sentence with approximately 15 tokens.";
        
        $tokenCount = $method->invoke($this->transcriptionService, $text);
        
        // Using the 4 characters ≈ 1 token approximation from the service
        $this->assertGreaterThan(10, $tokenCount);
        $this->assertLessThan(20, $tokenCount);
    }
} 