<?php
// [ai-generated-code]

namespace Tests\Unit\Services;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\SummaryGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class SummaryGenerationServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected SummaryGenerationService $service;
    protected Content $content;
    protected Transcript $transcript;
    
    public function setUp(): void
    {
        parent::setUp();
        
        // Create the service
        $this->service = new SummaryGenerationService();
        
        // Create test content and transcript
        $this->content = Content::factory()->create([
            'title' => 'Test Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=test123'
        ]);
        
        $this->transcript = new Transcript();
        $this->transcript->content_id = $this->content->id;
        $this->transcript->full_text = 'This is a test transcript for summary generation. It should be summarized by the service.';
        $this->transcript->language = 'en';
        $this->transcript->source_url = $this->content->source_url;
        $this->transcript->save();
    }
    
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test that the service returns null when the transcript text is empty
     */
    public function testGenerateSummaryReturnsNullWhenTextIsEmpty(): void
    {
        // Create a transcript with empty text
        $emptyTranscript = new Transcript();
        $emptyTranscript->content_id = $this->content->id;
        $emptyTranscript->full_text = '';
        $emptyTranscript->save();
        
        $result = $this->service->generateSummary($this->content, $emptyTranscript);
        
        $this->assertNull($result);
    }
    
    /**
     * Test that the service returns null when OpenAI API key is not configured
     */
    public function testGenerateSummaryReturnsNullWhenApiKeyNotConfigured(): void
    {
        // Mock the Config facade to return an empty API key
        Config::shouldReceive('get')
            ->with('openai.api_key')
            ->andReturn('your-openai-api-key-here');
        
        $result = $this->service->generateSummary($this->content, $this->transcript);
        
        $this->assertNull($result);
    }
    
    /**
     * Test the text truncation helper method
     */
    public function testTruncateTextPreservesWholeSeences(): void
    {
        // Use reflection to test the protected method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('truncateText');
        $method->setAccessible(true);
        
        $longText = "First sentence. Second sentence. Third sentence has some more words. Fourth sentence is the last one we want.";
        
        // Should keep all sentences when maxChars is large enough
        $result = $method->invoke($this->service, $longText, 200);
        $this->assertEquals($longText, $result);
        
        // Should truncate at the last complete sentence
        $result = $method->invoke($this->service, $longText, 50);
        $this->assertEquals("First sentence. Second sentence.", $result);
    }
} 