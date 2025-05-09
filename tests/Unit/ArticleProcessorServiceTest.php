<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\ArticleProcessorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ArticleProcessorServiceTest extends TestCase
{
    use RefreshDatabase;
    
    protected Content $content;
    protected ArticleProcessorService $articleService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test content
        $this->content = Content::factory()->create([
            'title' => 'Test Article',
            'source_type' => 'article',
            'source_url' => 'https://example.com/test-article'
        ]);
        
        $this->articleService = new ArticleProcessorService();
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
    
    /**
     * Test that article processing works with valid HTML
     */
    public function test_process_article_with_valid_content(): void
    {
        // Sample HTML response
        $sampleHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Test Article Title</title>
</head>
<body>
    <article>
        <h1>Main Article Heading</h1>
        <p>This is the first paragraph of the article content.</p>
        <p>This is the second paragraph with more detailed information.</p>
    </article>
    <script>
        // This should be removed
        console.log("Test script");
    </script>
</body>
</html>
HTML;

        // Mock HTTP responses
        Http::fake([
            'https://example.com/test-article' => Http::response($sampleHtml, 200)
        ]);
        
        // Process the article
        $transcript = $this->articleService->processArticle($this->content);
        
        // Assert transcript was created
        $this->assertInstanceOf(Transcript::class, $transcript);
        $this->assertEquals($this->content->id, $transcript->content_id);
        $this->assertEquals('en', $transcript->language);
        $this->assertEquals($this->content->source_url, $transcript->source_url);
        $this->assertTrue($transcript->processed);
        
        // Check that title was extracted and content was updated
        $this->content->refresh();
        $this->assertEquals('Test Article Title', $this->content->title);
        
        // Check that text was extracted properly (scripts removed)
        $this->assertStringContainsString('Main Article Heading', $transcript->full_text);
        $this->assertStringContainsString('first paragraph', $transcript->full_text);
        $this->assertStringContainsString('second paragraph', $transcript->full_text);
        $this->assertStringNotContainsString('console.log', $transcript->full_text);
        
        // Check that chunks were created
        $this->assertGreaterThan(0, $transcript->chunks()->count());
    }
    
    /**
     * Test handling failed HTTP requests
     */
    public function test_process_article_with_failed_request(): void
    {
        // Mock HTTP responses
        Http::fake([
            'https://example.com/test-article' => Http::response('Not Found', 404)
        ]);
        
        // Process should return null for failed requests
        $result = $this->articleService->processArticle($this->content);
        
        $this->assertNull($result);
    }
    
    /**
     * Test that the chunking functionality works properly
     */
    public function test_text_chunking(): void
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(ArticleProcessorService::class, 'chunkText');
        $method->setAccessible(true);
        
        $sampleText = <<<EOT
Paragraph one with some content.

Paragraph two with more content.

Paragraph three to test chunking.
EOT;
        
        $chunks = $method->invoke($this->articleService, $sampleText);
        
        $this->assertIsArray($chunks);
        $this->assertNotEmpty($chunks);
        $this->assertStringContainsString('Paragraph one', $chunks[0]);
    }
    
    /**
     * Test that token counting works approximately as expected
     */
    public function test_token_counting(): void
    {
        // Use reflection to access protected method
        $method = new \ReflectionMethod(ArticleProcessorService::class, 'countTokens');
        $method->setAccessible(true);
        
        $text = "This is a test sentence with approximately 15 tokens.";
        
        $tokenCount = $method->invoke($this->articleService, $text);
        
        // Using the 4 characters ≈ 1 token approximation from the service
        $this->assertGreaterThan(10, $tokenCount);
        $this->assertLessThan(20, $tokenCount);
    }
} 