<?php
// [ai-generated-code]

namespace Tests\Unit;

use App\Models\Content;
use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_has_correct_fillable_attributes(): void
    {
        $content = new Content();
        
        $this->assertEquals([
            'title',
            'source_type',
            'source_url',
            'summary',
        ], $content->getFillable());
    }

    public function test_content_can_be_created(): void
    {
        $contentData = [
            'title' => 'Test Content',
            'source_type' => 'Article',
            'source_url' => 'https://example.com/test',
            'summary' => 'This is a test summary'
        ];

        $content = Content::create($contentData);
        
        $this->assertDatabaseHas('contents', $contentData);
        $this->assertInstanceOf(Content::class, $content);
    }

    public function test_content_has_nodes_relationship(): void
    {
        $content = Content::factory()->create();
        $node = Node::factory()->create();
        
        $content->nodes()->attach($node->id);
        
        $this->assertTrue($content->nodes->contains($node));
        $this->assertInstanceOf(Node::class, $content->nodes->first());
    }

    public function test_to_searchable_array(): void
    {
        $content = Content::factory()->create([
            'title' => 'Test Searchable',
            'source_type' => 'Video',
            'summary' => 'Test searchable summary'
        ]);
        
        $searchableArray = $content->toSearchableArray();
        
        $this->assertEquals([
            'id' => $content->id,
            'title' => 'Test Searchable',
            'source_type' => 'Video',
            'summary' => 'Test searchable summary',
        ], $searchableArray);
    }

    public function test_should_be_searchable(): void
    {
        $content = Content::factory()->create();
        
        // Default config value should make it searchable
        $this->assertTrue($content->shouldBeSearchable());
        
        // Test when disabled via config
        config(['scout.enabled' => false]);
        $this->assertFalse($content->shouldBeSearchable());
    }
} 