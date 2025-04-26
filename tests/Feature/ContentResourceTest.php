<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Http\Resources\ContentResource;
use App\Models\Content;
use App\Models\Node;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_content_resource_has_correct_structure(): void
    {
        // Create a content with associated nodes
        $content = Content::factory()->create([
            'title' => 'Test Resource Content',
            'source_type' => 'Article',
            'source_url' => 'https://example.com/test-resource',
            'summary' => 'Test resource summary content',
        ]);
        
        // Create and attach nodes
        $node1 = Node::factory()->create(['label' => 'Test Node 1']);
        $node2 = Node::factory()->create(['label' => 'Test Node 2']);
        $content->nodes()->attach([$node1->id, $node2->id]);
        
        // Load the nodes relationship
        $content->load('nodes');
        
        // Create the resource
        $resource = new ContentResource($content);
        
        // Convert to array
        $resourceArray = $resource->toArray(request());
        
        // Assert structure and values
        $this->assertArrayHasKey('id', $resourceArray);
        $this->assertArrayHasKey('title', $resourceArray);
        $this->assertArrayHasKey('source_type', $resourceArray);
        $this->assertArrayHasKey('source_url', $resourceArray);
        $this->assertArrayHasKey('summary', $resourceArray);
        $this->assertArrayHasKey('created_at', $resourceArray);
        $this->assertArrayHasKey('updated_at', $resourceArray);
        $this->assertArrayHasKey('nodes', $resourceArray);
        
        // Assert values
        $this->assertEquals($content->id, $resourceArray['id']);
        $this->assertEquals('Test Resource Content', $resourceArray['title']);
        $this->assertEquals('Article', $resourceArray['source_type']);
        $this->assertEquals('https://example.com/test-resource', $resourceArray['source_url']);
        $this->assertEquals('Test resource summary content', $resourceArray['summary']);
        
        // Assert nodes are included and have correct structure
        $this->assertCount(2, $resourceArray['nodes']);
        $this->assertEquals($node1->id, $resourceArray['nodes'][0]['id']);
        $this->assertEquals('Test Node 1', $resourceArray['nodes'][0]['label']);
        $this->assertEquals($node2->id, $resourceArray['nodes'][1]['id']);
        $this->assertEquals('Test Node 2', $resourceArray['nodes'][1]['label']);
    }
    
    public function test_content_resource_collection(): void
    {
        // Create multiple contents
        Content::factory()->count(3)->create();
        
        // Get all contents
        $contents = Content::all();
        
        // Create resource collection
        $resourceCollection = ContentResource::collection($contents);
        
        // Convert to array
        $resourceArray = $resourceCollection->toArray(request());
        
        // Assert collection count
        $this->assertCount(3, $resourceArray);
        
        // Assert each resource has the expected structure
        foreach ($resourceArray as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('title', $item);
            $this->assertArrayHasKey('source_type', $item);
            $this->assertArrayHasKey('source_url', $item);
            $this->assertArrayHasKey('summary', $item);
            $this->assertArrayHasKey('created_at', $item);
            $this->assertArrayHasKey('updated_at', $item);
        }
    }
} 