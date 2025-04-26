<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication tests
        $this->user = User::factory()->create();
    }
    
    /**
     * Test controller index method returns all contents
     */
    public function test_index_returns_all_contents(): void
    {
        // Create content items
        Content::factory()->count(3)->create();
        
        // Make request to index endpoint
        $response = $this->getJson('/api/contents');
        
        // Assert response status and structure
        $response->assertStatus(200)
                ->assertJsonStructure(['data'])
                ->assertJsonCount(3, 'data');
    }
    
    /**
     * Test controller show method returns a specific content
     */
    public function test_show_returns_content_with_nodes(): void
    {
        // Create content with associated nodes
        $content = Content::factory()->create();
        $nodes = Node::factory()->count(2)->create();
        $content->nodes()->attach($nodes->pluck('id'));
        
        // Make request to show endpoint
        $response = $this->getJson("/api/contents/{$content->id}");
        
        // Assert response status and structure
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 'title', 'source_type', 'source_url', 'summary', 
                        'created_at', 'updated_at', 'nodes'
                    ]
                ])
                ->assertJsonCount(2, 'data.nodes');
    }
    
    /**
     * Test controller store method creates content
     */
    public function test_store_creates_content_with_nodes(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Create nodes to associate with content
        $nodes = Node::factory()->count(2)->create();
        $nodeIds = $nodes->pluck('id')->toArray();
        
        // Prepare content data
        $contentData = [
            'title' => 'Test Controller Content',
            'source_type' => 'Article',
            'source_url' => 'https://example.com/controller-test',
            'summary' => 'Test summary for controller test',
            'node_ids' => $nodeIds,
        ];
        
        // Make request to store endpoint
        $response = $this->postJson('/api/contents', $contentData);
        
        // Assert response status and structure
        $response->assertStatus(201)
                ->assertJsonPath('data.title', $contentData['title'])
                ->assertJsonPath('data.source_type', $contentData['source_type'])
                ->assertJsonPath('data.source_url', $contentData['source_url'])
                ->assertJsonPath('data.summary', $contentData['summary'])
                ->assertJsonCount(2, 'data.nodes');
        
        // Assert content was created in database
        $this->assertDatabaseHas('contents', [
            'title' => $contentData['title'],
            'source_type' => $contentData['source_type'],
        ]);
        
        // Assert relationship was created in pivot table
        $contentId = $response->json('data.id');
        foreach ($nodeIds as $nodeId) {
            $this->assertDatabaseHas('content_node', [
                'content_id' => $contentId,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test store method validation errors
     */
    public function test_store_validates_input(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Make request with invalid data (missing required fields)
        $response = $this->postJson('/api/contents', [
            'source_url' => 'https://example.com',
            'summary' => 'Test summary',
        ]);
        
        // Assert validation errors
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'source_type']);
    }
    
    /**
     * Test controller update method
     */
    public function test_update_modifies_content(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Create content to update
        $content = Content::factory()->create();
        
        // Create nodes to associate with content
        $nodes = Node::factory()->count(2)->create();
        $nodeIds = $nodes->pluck('id')->toArray();
        
        // Prepare update data
        $updateData = [
            'title' => 'Updated Title',
            'summary' => 'Updated summary text',
            'node_ids' => $nodeIds,
        ];
        
        // Make request to update endpoint
        $response = $this->putJson("/api/contents/{$content->id}", $updateData);
        
        // Assert response status and content
        $response->assertStatus(200)
                ->assertJsonPath('data.title', 'Updated Title')
                ->assertJsonPath('data.summary', 'Updated summary text')
                ->assertJsonCount(2, 'data.nodes');
        
        // Assert content was updated in database
        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => 'Updated Title',
            'summary' => 'Updated summary text',
        ]);
        
        // Assert relationships were updated
        foreach ($nodeIds as $nodeId) {
            $this->assertDatabaseHas('content_node', [
                'content_id' => $content->id,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test controller destroy method
     */
    public function test_destroy_deletes_content(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Create content to delete
        $content = Content::factory()->create();
        
        // Make request to destroy endpoint
        $response = $this->deleteJson("/api/contents/{$content->id}");
        
        // Assert response status
        $response->assertStatus(204);
        
        // Assert content was deleted from database
        $this->assertDatabaseMissing('contents', ['id' => $content->id]);
    }
    
    /**
     * Test addNodes method
     */
    public function test_add_nodes_attaches_nodes_to_content(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Create content
        $content = Content::factory()->create();
        
        // Create nodes to add
        $nodes = Node::factory()->count(3)->create();
        $nodeIds = $nodes->pluck('id')->toArray();
        
        // Make request to add nodes
        $response = $this->postJson("/api/contents/{$content->id}/nodes", [
            'node_ids' => $nodeIds,
        ]);
        
        // Assert response status
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data.nodes');
        
        // Assert relationships were created
        foreach ($nodeIds as $nodeId) {
            $this->assertDatabaseHas('content_node', [
                'content_id' => $content->id,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test removeNodes method
     */
    public function test_remove_nodes_detaches_nodes_from_content(): void
    {
        // Authenticate user
        Passport::actingAs($this->user);
        
        // Create content
        $content = Content::factory()->create();
        
        // Create and attach nodes
        $nodes = Node::factory()->count(3)->create();
        $content->nodes()->attach($nodes->pluck('id'));
        
        // Select nodes to remove
        $nodesToRemove = $nodes->take(2);
        $nodeIdsToRemove = $nodesToRemove->pluck('id')->toArray();
        
        // Make request to remove nodes
        $response = $this->deleteJson("/api/contents/{$content->id}/nodes", [
            'node_ids' => $nodeIdsToRemove,
        ]);
        
        // Assert response status
        $response->assertStatus(200)
                ->assertJsonCount(1, 'data.nodes');
        
        // Assert relationships were removed
        foreach ($nodeIdsToRemove as $nodeId) {
            $this->assertDatabaseMissing('content_node', [
                'content_id' => $content->id,
                'node_id' => $nodeId,
            ]);
        }
        
        // Assert remaining node is still attached
        $remainingNodeId = $nodes->last()->id;
        $this->assertDatabaseHas('content_node', [
            'content_id' => $content->id,
            'node_id' => $remainingNodeId,
        ]);
    }
    
    /**
     * Test authentication requirements
     */
    public function test_protected_endpoints_require_authentication(): void
    {
        // Create content
        $content = Content::factory()->create();
        
        // Test store endpoint
        $this->postJson('/api/contents', ['title' => 'Test'])
             ->assertStatus(401);
        
        // Test update endpoint
        $this->putJson("/api/contents/{$content->id}", ['title' => 'Updated'])
             ->assertStatus(401);
        
        // Test destroy endpoint
        $this->deleteJson("/api/contents/{$content->id}")
             ->assertStatus(401);
        
        // Test add nodes endpoint
        $this->postJson("/api/contents/{$content->id}/nodes", ['node_ids' => [1]])
             ->assertStatus(401);
        
        // Test remove nodes endpoint
        $this->deleteJson("/api/contents/{$content->id}/nodes", ['node_ids' => [1]])
             ->assertStatus(401);
    }
} 