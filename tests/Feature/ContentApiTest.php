<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Node;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Database\Seeders\NodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database with nodes and contents
        $this->seed(NodeSeeder::class);
        $this->seed(ContentSeeder::class);
    }
    
    /**
     * Test listing all contents
     */
    public function test_can_list_all_contents(): void
    {
        $response = $this->getJson('/api/contents');
        
        $response->assertStatus(200)
                ->assertJsonStructure(['data']);
    }
    
    /**
     * Test viewing a single content
     */
    public function test_can_view_single_content(): void
    {
        $content = Content::first();
        
        $response = $this->getJson("/api/contents/{$content->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 
                        'title', 
                        'source_type', 
                        'source_url', 
                        'summary', 
                        'created_at', 
                        'updated_at',
                        'nodes'
                    ]
                ]);
    }
    
    /**
     * Test creating a content (requires authentication)
     */
    public function test_can_create_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Get some nodes to associate with the content
        $nodes = Node::take(2)->get();
        $nodeIds = $nodes->pluck('id')->toArray();
        
        $contentData = [
            'title' => 'Test Content',
            'source_type' => 'Article',
            'source_url' => 'https://example.com/test-article',
            'summary' => 'This is a test content item',
            'node_ids' => $nodeIds,
        ];
        
        $response = $this->postJson('/api/contents', $contentData);
        
        $response->assertStatus(201)
                ->assertJsonPath('data.title', $contentData['title'])
                ->assertJsonPath('data.source_type', $contentData['source_type'])
                ->assertJsonPath('data.source_url', $contentData['source_url'])
                ->assertJsonPath('data.summary', $contentData['summary']);
                
        $this->assertDatabaseHas('contents', [
            'title' => $contentData['title'],
            'source_type' => $contentData['source_type'],
        ]);
        
        // Check if nodes were properly attached
        $contentId = $response->json('data.id');
        foreach ($nodeIds as $nodeId) {
            $this->assertDatabaseHas('content_node', [
                'content_id' => $contentId,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test updating a content (requires authentication)
     */
    public function test_can_update_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $content = Content::first();
        
        $updateData = [
            'title' => 'Updated Content Title',
            'summary' => 'This is an updated summary for testing purposes',
        ];
        
        $response = $this->putJson("/api/contents/{$content->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJsonPath('data.title', $updateData['title'])
                ->assertJsonPath('data.summary', $updateData['summary']);
                
        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => $updateData['title'],
            'summary' => $updateData['summary'],
        ]);
    }
    
    /**
     * Test deleting a content (requires authentication)
     */
    public function test_can_delete_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $content = Content::first();
        
        $response = $this->deleteJson("/api/contents/{$content->id}");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('contents', [
            'id' => $content->id,
        ]);
    }
    
    /**
     * Test adding nodes to a content
     */
    public function test_can_add_nodes_to_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $content = Content::first();
        
        // Find nodes that are not already associated with this content
        $existingNodeIds = $content->nodes->pluck('id')->toArray();
        $newNodes = Node::whereNotIn('id', $existingNodeIds)->take(2)->get();
        $newNodeIds = $newNodes->pluck('id')->toArray();
        
        $response = $this->postJson("/api/contents/{$content->id}/nodes", [
            'node_ids' => $newNodeIds,
        ]);
        
        $response->assertStatus(200);
        
        // Check if nodes were added to the content
        foreach ($newNodeIds as $nodeId) {
            $this->assertDatabaseHas('content_node', [
                'content_id' => $content->id,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test removing nodes from a content
     */
    public function test_can_remove_nodes_from_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Find a content with associated nodes
        $content = Content::has('nodes')->first();
        
        if (!$content) {
            $this->markTestSkipped('No content with associated nodes found.');
        }
        
        $nodeIdsToRemove = $content->nodes->take(1)->pluck('id')->toArray();
        
        $response = $this->deleteJson("/api/contents/{$content->id}/nodes", [
            'node_ids' => $nodeIdsToRemove,
        ]);
        
        $response->assertStatus(200);
        
        // Check if nodes were removed from the content
        foreach ($nodeIdsToRemove as $nodeId) {
            $this->assertDatabaseMissing('content_node', [
                'content_id' => $content->id,
                'node_id' => $nodeId,
            ]);
        }
    }
    
    /**
     * Test unauthorized access to protected endpoints
     */
    public function test_unauthorized_access_is_rejected(): void
    {
        $contentData = [
            'title' => 'Test Content',
            'source_type' => 'Article',
        ];
        
        // Test creation without auth
        $this->postJson('/api/contents', $contentData)
             ->assertStatus(401);
        
        // Test update without auth
        $content = Content::first();
        $this->putJson("/api/contents/{$content->id}", ['title' => 'Updated'])
             ->assertStatus(401);
        
        // Test deletion without auth
        $this->deleteJson("/api/contents/{$content->id}")
             ->assertStatus(401);
        
        // Test adding nodes without auth
        $nodeIds = Node::take(1)->pluck('id')->toArray();
        $this->postJson("/api/contents/{$content->id}/nodes", ['node_ids' => $nodeIds])
             ->assertStatus(401);
        
        // Test removing nodes without auth
        $this->deleteJson("/api/contents/{$content->id}/nodes", ['node_ids' => $nodeIds])
             ->assertStatus(401);
    }
} 