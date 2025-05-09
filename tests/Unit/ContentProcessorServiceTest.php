<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Services\ContentProcessorService;
use Tests\TestCase;

class ContentProcessorServiceTest extends TestCase
{
    protected ContentProcessorService $processorService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->processorService = new ContentProcessorService();
    }

    /**
     * Test source type detection for YouTube URLs
     */
    public function test_determines_youtube_source_type(): void
    {
        $urls = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&feature=shared',
        ];

        foreach ($urls as $url) {
            $sourceType = $this->processorService->determineSourceType($url);
            $this->assertEquals('youtube', $sourceType, "URL $url should be detected as a YouTube link");
        }
    }

    /**
     * Test source type detection for article URLs
     */
    public function test_determines_article_source_type(): void
    {
        $urls = [
            'https://example.com/article/123',
            'https://medium.com/article/some-article-title',
            'https://dev.to/article/some-title',
        ];

        foreach ($urls as $url) {
            $sourceType = $this->processorService->determineSourceType($url);
            $this->assertEquals('article', $sourceType, "URL $url should be detected as an article link");
        }
    }

    /**
     * Test YouTube ID extraction
     */
    public function test_extracts_youtube_id(): void
    {
        $testCases = [
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://youtu.be/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=dQw4w9WgXcQ&feature=shared' => 'dQw4w9WgXcQ',
            'https://youtube.com/watch?v=dQw4w9WgXcQ&t=120s' => 'dQw4w9WgXcQ',
            'https://www.youtube.com/embed/dQw4w9WgXcQ' => 'dQw4w9WgXcQ',
            'https://invalid-url.com' => null,
        ];

        foreach ($testCases as $url => $expectedId) {
            $id = $this->processorService->extractYoutubeId($url);
            $this->assertEquals($expectedId, $id, "The YouTube ID for URL $url should be $expectedId");
        }
    }

    /**
     * Test content creation from URL
     */
    public function test_processes_content_from_url(): void
    {
        $url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        
        $content = $this->processorService->processFromUrl($url);
        
        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals($url, $content->source_url);
        $this->assertEquals('youtube', $content->source_type);
        $this->assertEquals('Processing youtube content...', $content->title);
        $this->assertNotNull($content->id);
        
        // Clean up test data
        $content->delete();
    }
} 