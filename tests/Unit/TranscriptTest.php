<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Transcript;
use App\Models\TranscriptChunk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TranscriptTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_transcript_with_chunks()
    {
        // Create a content item
        $content = Content::create([
            'title' => 'Test YouTube Video',
            'source_type' => 'youtube',
            'source_url' => 'https://www.youtube.com/watch?v=abcdefgh123',
            'summary' => 'A test video for our transcript system',
        ]);

        // Create a transcript
        $transcript = new Transcript();
        $transcript->content_id = $content->id;
        $transcript->full_text = 'This is a test transcript with multiple sentences. This is the second sentence. And this is the third sentence.';
        $transcript->language = 'en';
        $transcript->duration_seconds = 60;
        $transcript->token_count = 25;
        $transcript->source_url = 'https://www.youtube.com/watch?v=abcdefgh123';
        $transcript->metadata = ['video_id' => 'abcdefgh123'];
        $transcript->processed = true;
        $transcript->save();

        // Create chunks
        $chunk1 = new TranscriptChunk();
        $chunk1->transcript_id = $transcript->id;
        $chunk1->chunk_index = 0;
        $chunk1->text = 'This is a test transcript with multiple sentences.';
        $chunk1->start_time = 0;
        $chunk1->end_time = 20;
        $chunk1->token_count = 10;
        $chunk1->save();

        $chunk2 = new TranscriptChunk();
        $chunk2->transcript_id = $transcript->id;
        $chunk2->chunk_index = 1;
        $chunk2->text = 'This is the second sentence. And this is the third sentence.';
        $chunk2->start_time = 21;
        $chunk2->end_time = 60;
        $chunk2->token_count = 15;
        $chunk2->save();

        // Test relationships
        $this->assertEquals($content->id, $transcript->content->id);
        $this->assertCount(2, $transcript->chunks);
        $this->assertEquals($transcript->id, $chunk1->transcript->id);
        
        // Test content relationship to transcript
        $this->assertEquals($transcript->id, $content->transcript->id);

        // Test time-based retrieval
        $chunkAt10Seconds = $transcript->getChunkByTime(10);
        $this->assertEquals($chunk1->id, $chunkAt10Seconds->id);

        $chunkAt30Seconds = $transcript->getChunkByTime(30);
        $this->assertEquals($chunk2->id, $chunkAt30Seconds->id);

        // Test time range retrieval
        $chunksInRange = $transcript->getChunksInTimeRange(15, 25);
        $this->assertCount(2, $chunksInRange);

        // Test formatted time methods
        $this->assertEquals('00:21', $chunk2->formatted_start_time);
        $this->assertEquals('01:00', $chunk2->formatted_end_time);
    }
} 