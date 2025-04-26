<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Node;
use App\Models\User;
use App\Services\EmbeddingService;
use Database\Seeders\NodeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Mockery;

class NodeApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed database with some nodes
        $this->seed(NodeSeeder::class);
    }
    
    /**
     * Test listing all nodes
     */
    public function test_can_list_all_nodes(): void
    {
        $response = $this->getJson('/api/nodes');
        
        $response->assertStatus(200)
                ->assertJsonStructure(['data'])
                ->assertJsonCount(24, 'data'); // 20 random + 4 predefined
    }
    
    /**
     * Test viewing a single node
     */
    public function test_can_view_single_node(): void
    {
        $node = Node::first();
        
        $response = $this->getJson("/api/nodes/{$node->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id', 
                        'label', 
                        'description', 
                        'type', 
                        'source', 
                        'color', 
                        'has_embedding', 
                        'created_at', 
                        'updated_at'
                    ]
                ]);
    }
    
    /**
     * Test creating a node (requires authentication)
     */
    public function test_can_create_node(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $nodeData = [
            'label' => 'Test Node',
            'description' => 'This is a test node',
            'type' => 'Test',
            'source' => 'PHPUnit Test',
            'color' => '#00FF00',
        ];
        
        $response = $this->postJson('/api/nodes', $nodeData);
        
        $response->assertStatus(201)
                ->assertJsonPath('data.label', $nodeData['label'])
                ->assertJsonPath('data.description', $nodeData['description'])
                ->assertJsonPath('data.type', $nodeData['type'])
                ->assertJsonPath('data.source', $nodeData['source'])
                ->assertJsonPath('data.color', $nodeData['color']);
                
        $this->assertDatabaseHas('nodes', [
            'label' => $nodeData['label'],
            'type' => $nodeData['type'],
        ]);
    }
    
    /**
     * Test updating a node (requires authentication)
     */
    public function test_can_update_node(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $node = Node::first();
        
        $updateData = [
            'label' => 'Updated Node Label',
            'color' => '#FF0000',
        ];
        
        $response = $this->putJson("/api/nodes/{$node->id}", $updateData);
        
        $response->assertStatus(200)
                ->assertJsonPath('data.label', $updateData['label'])
                ->assertJsonPath('data.color', $updateData['color']);
                
        $this->assertDatabaseHas('nodes', [
            'id' => $node->id,
            'label' => $updateData['label'],
            'color' => $updateData['color'],
        ]);
    }
    
    /**
     * Test deleting a node (requires authentication)
     */
    public function test_can_delete_node(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        $node = Node::first();
        
        $response = $this->deleteJson("/api/nodes/{$node->id}");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('nodes', [
            'id' => $node->id,
        ]);
    }
    
    /**
     * Test unauthorized access to protected endpoints
     */
    public function test_unauthorized_access_is_rejected(): void
    {
        $nodeData = [
            'label' => 'Test Node',
            'type' => 'Test',
        ];
        
        // Test creation without auth
        $this->postJson('/api/nodes', $nodeData)
             ->assertStatus(401);
        
        // Test update without auth
        $node = Node::first();
        $this->putJson("/api/nodes/{$node->id}", ['label' => 'Updated'])
             ->assertStatus(401);
        
        // Test deletion without auth
        $this->deleteJson("/api/nodes/{$node->id}")
             ->assertStatus(401);
    }

    /**
     * Test that embedding is generated on node creation
     */
    public function test_embedding_is_generated_when_creating_node(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Mock the embedding service
        $mockEmbedding = [0.1, 0.2, 0.3, 0.4, 0.5];
        $embeddingService = Mockery::mock(EmbeddingService::class);
        $embeddingService->shouldReceive('generateEmbedding')
            ->once()
            ->andReturn($mockEmbedding);
        $this->app->instance(EmbeddingService::class, $embeddingService);
        
        $nodeData = [
            'label' => 'Embedding Test Node',
            'description' => 'This node should get an embedding',
            'type' => 'Test',
        ];
        
        $response = $this->postJson('/api/nodes', $nodeData);
        
        $response->assertStatus(201)
                ->assertJsonPath('data.has_embedding', true);
                
        // Get the created node and check that it has an embedding
        $nodeId = $response->json('data.id');
        $node = Node::find($nodeId);
        $this->assertNotNull($node->embedding);
        $this->assertEquals($mockEmbedding, $node->embedding);
    }
    
    /**
     * Test that embedding is regenerated when node content is updated
     */
    public function test_embedding_is_regenerated_when_updating_node_content(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Create a node with initial embedding
        $initialEmbedding = [0.1, 0.2, 0.3, 0.4, 0.5];
        $node = Node::factory()->create([
            'label' => 'Initial Label',
            'description' => 'Initial description',
            'embedding' => $initialEmbedding
        ]);
        
        // Mock the embedding service for the update
        $updatedEmbedding = [0.5, 0.4, 0.3, 0.2, 0.1];
        $embeddingService = Mockery::mock(EmbeddingService::class);
        $embeddingService->shouldReceive('generateEmbedding')
            ->once()
            ->andReturn($updatedEmbedding);
        $this->app->instance(EmbeddingService::class, $embeddingService);
        
        // Update the node's content
        $updateData = [
            'label' => 'Updated Label',
            'description' => 'Updated description',
        ];
        
        $response = $this->putJson("/api/nodes/{$node->id}", $updateData);
        
        $response->assertStatus(200);
        
        // Verify that the embedding was updated
        $node->refresh();
        $this->assertEquals($updatedEmbedding, $node->embedding);
    }
    
    /**
     * Test that embedding is not regenerated when non-content fields are updated
     */
    public function test_embedding_is_not_regenerated_when_updating_non_content_fields(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Create a node with initial embedding
        $initialEmbedding = [0.1, 0.2, 0.3, 0.4, 0.5];
        $node = Node::factory()->create([
            'label' => 'Test Label',
            'description' => 'Test description',
            'color' => '#111111',
            'embedding' => $initialEmbedding
        ]);
        
        // Mock the embedding service - it should NOT be called
        $embeddingService = Mockery::mock(EmbeddingService::class);
        $embeddingService->shouldNotReceive('generateEmbedding');
        $this->app->instance(EmbeddingService::class, $embeddingService);
        
        // Update only non-content fields
        $updateData = [
            'color' => '#222222',
            'source' => 'Updated Source',
        ];
        
        $response = $this->putJson("/api/nodes/{$node->id}", $updateData);
        
        $response->assertStatus(200);
        
        // Verify that the embedding was not changed
        $node->refresh();
        $this->assertEquals($initialEmbedding, $node->embedding);
    }
    
    /**
     * Test finding similar nodes based on embedding
     */
    public function test_can_find_similar_nodes(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Create test nodes with embeddings
        $sourceNode = Node::factory()->create([
            'label' => 'Source Node',
            'embedding' => [0.9, 0.8, 0.7, 0.6, 0.5]
        ]);
        
        // Create some nodes with varying similarity
        $highSimilarityNode = Node::factory()->create([
            'label' => 'Very Similar Node',
            'embedding' => [0.91, 0.79, 0.71, 0.59, 0.52] // Very close to source
        ]);
        
        $mediumSimilarityNode = Node::factory()->create([
            'label' => 'Somewhat Similar Node',
            'embedding' => [0.7, 0.6, 0.5, 0.4, 0.3] // Moderately similar
        ]);
        
        $lowSimilarityNode = Node::factory()->create([
            'label' => 'Not Similar Node',
            'embedding' => [-0.9, -0.8, -0.7, -0.6, -0.5] // Almost opposite
        ]);
        
        // Mock the embedding service for similarity calculations
        $embeddingService = Mockery::mock(EmbeddingService::class);
        $embeddingService->shouldReceive('cosineSimilarity')
            ->withArgs(function($arg1, $arg2) {
                // Match any vectors of same length
                return is_array($arg1) && is_array($arg2) && count($arg1) == count($arg2);
            })
            ->andReturnUsing(function($vec1, $vec2) {
                if (isset($vec1[0]) && $vec1[0] == 0.9 && isset($vec2[0]) && abs($vec2[0] - 0.91) < 0.1) {
                    return 0.98; // High similarity
                } elseif (isset($vec1[0]) && $vec1[0] == 0.9 && isset($vec2[0]) && abs($vec2[0] - 0.7) < 0.1) {
                    return 0.65; // Medium similarity
                } else {
                    return -0.95; // Low similarity
                }
            });
        
        $this->app->instance(EmbeddingService::class, $embeddingService);
        
        // Request similar nodes with limit of 2
        $response = $this->getJson("/api/nodes/{$sourceNode->id}/similar?limit=2");
        
        $response->assertStatus(200)
                ->assertJsonCount(2);
        
        // Check that the response contains nodes with similarity values
        $this->assertStringContainsString('similarity', $response->getContent());
        $this->assertStringContainsString('node', $response->getContent());
    }
    
    /**
     * Test error response when trying to find similar nodes for a node without embedding
     */
    public function test_find_similar_returns_error_when_node_has_no_embedding(): void
    {
        // Create and authenticate as a user
        $user = User::factory()->create();
        Passport::actingAs($user);
        
        // Create a node without embedding
        $node = Node::factory()->create([
            'label' => 'Node without embedding',
            'embedding' => null
        ]);
        
        $response = $this->getJson("/api/nodes/{$node->id}/similar");
        
        $response->assertStatus(400)
                ->assertJson(['error' => 'Node has no embedding vector']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 