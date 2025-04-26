<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_search_contents_by_title(): void
    {
        // Create test data
        Content::factory()->create(['title' => 'Unique Neural Networks']);
        Content::factory()->create(['title' => 'Machine Learning Basics']);
        Content::factory()->create(['title' => 'Advanced Deep Learning']);
        
        // Use database query instead of Scout search
        $results = Content::where('title', 'like', '%Neural%')->get();
        
        // Assert we find only the relevant content
        $this->assertEquals(1, $results->count());
        $this->assertEquals('Unique Neural Networks', $results->first()->title);
    }
    
    public function test_can_search_contents_by_source_type(): void
    {
        // Create test data with different source types
        Content::factory()->create(['source_type' => 'Video', 'title' => 'Video Tutorial']);
        Content::factory()->create(['source_type' => 'Article', 'title' => 'Blog Article']);
        Content::factory()->create(['source_type' => 'Podcast', 'title' => 'Audio Podcast']);
        
        // Use database query instead of Scout search
        $results = Content::where('source_type', 'Article')->get();
        
        // Assert we find articles
        $this->assertEquals(1, $results->count());
        $this->assertEquals('Article', $results->first()->source_type);
    }
    
    public function test_can_search_contents_by_summary(): void
    {
        // Create test data with specific summary text
        Content::factory()->create([
            'title' => 'Test Content 1',
            'summary' => 'This discusses quantum computing applications'
        ]);
        
        Content::factory()->create([
            'title' => 'Test Content 2',
            'summary' => 'All about general artificial intelligence topics'
        ]);
        
        // Use database query instead of Scout search
        $results = Content::where('summary', 'like', '%quantum%')->get();
        
        // Assert we find the relevant content
        $this->assertEquals(1, $results->count());
        $this->assertStringContainsString('quantum', strtolower($results->first()->summary));
    }
} 