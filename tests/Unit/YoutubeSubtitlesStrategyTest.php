<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Services\TranscriptionStrategies\YoutubeSubtitlesStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class YoutubeSubtitlesStrategyTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test the canHandle method for YouTube URLs
     */
    public function test_can_handle_youtube_urls(): void
    {
        $strategy = new YoutubeSubtitlesStrategy();
        
        // YouTube URLs that should be handled
        $youtubeContent = new Content(['source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']);
        $youtuBeContent = new Content(['source_url' => 'https://youtu.be/dQw4w9WgXcQ']);
        $youtubeShortContent = new Content(['source_url' => 'http://youtube.com/watch?v=dQw4w9WgXcQ']);
        
        // Non-YouTube URLs that should not be handled
        $vimeoContent = new Content(['source_url' => 'https://vimeo.com/123456789']);
        $regularUrl = new Content(['source_url' => 'https://example.com/video']);
        
        $this->assertTrue($strategy->canHandle($youtubeContent));
        $this->assertTrue($strategy->canHandle($youtuBeContent));
        $this->assertTrue($strategy->canHandle($youtubeShortContent));
        $this->assertFalse($strategy->canHandle($vimeoContent));
        $this->assertFalse($strategy->canHandle($regularUrl));
    }
    
    /**
     * Test subtitle availability check
     */
    public function test_check_subtitles_available(): void
    {
        // Skip test if not running in CI environment to avoid network calls
        if (!getenv('CI')) {
            $this->markTestSkipped('Skipping external API call in non-CI environment');
        }
        
        $strategy = new YoutubeSubtitlesStrategy();
        $reflectionMethod = new \ReflectionMethod($strategy, 'checkSubtitlesAvailable');
        $reflectionMethod->setAccessible(true);
        
        // Video known to have English subtitles
        $subtitlesAvailable = $reflectionMethod->invoke($strategy, 'dQw4w9WgXcQ');
        
        $this->assertTrue($subtitlesAvailable);
    }
    
    /**
     * Test that the fetchSubtitles method retrieves subtitle content
     */
    public function test_fetch_subtitles(): void
    {
        // Skip test if not running in CI environment to avoid network calls
        if (!getenv('CI')) {
            $this->markTestSkipped('Skipping external API call in non-CI environment');
        }
        
        $strategy = new YoutubeSubtitlesStrategy();
        $reflectionMethod = new \ReflectionMethod($strategy, 'fetchSubtitles');
        $reflectionMethod->setAccessible(true);
        
        // Video known to have English subtitles
        $subtitlesContent = $reflectionMethod->invoke($strategy, 'dQw4w9WgXcQ');
        
        $this->assertNotNull($subtitlesContent);
        $this->assertNotEmpty($subtitlesContent);
    }
    
    /**
     * Test the full transcription process for a YouTube video
     */
    public function test_transcribe_process(): void
    {
        // Skip test if not running in CI environment to avoid network calls
        if (!getenv('CI')) {
            $this->markTestSkipped('Skipping external API call in non-CI environment');
        }
        
        // Mock the FetchVideoDetailsCommand to return a fixed result
        $this->mock(\App\Services\Commands\FetchVideoDetailsCommand::class, function ($mock) {
            $mock->shouldReceive('execute')
                ->with('dQw4w9WgXcQ', 'youtube')
                ->andReturn([
                    'title' => 'Rick Astley - Never Gonna Give You Up (Official Music Video)',
                    'description' => 'Test video description',
                    'duration' => 213
                ]);
        });
        
        $strategy = new YoutubeSubtitlesStrategy();
        $content = Content::create([
            'source_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'title' => 'YouTube Test Video',
            'description' => 'A test video',
            'source_type' => 'youtube'
        ]);
        
        $transcript = $strategy->transcribe($content, 'dQw4w9WgXcQ');
        
        $this->assertNotNull($transcript);
        if ($transcript) {
            $this->assertEquals($content->id, $transcript->content_id);
            $this->assertNotEmpty($transcript->full_text);
            $this->assertEquals('youtube_subtitles', $transcript->metadata['source_type']);
        }
    }
} 