<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Node;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\Passport;
use Tests\TestCase;

class NodeStructureTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authenticated tests
        $this->user = User::factory()->create();
    }

    /**
     * Test that the nodes table has the expected columns
     */
    public function test_nodes_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('nodes'));
        
        $expectedColumns = [
            'id',
            'label',
            'description',
            'type',
            'source',
            'embedding',
            'color',
            'created_at',
            'updated_at'
        ];
        
        foreach ($expectedColumns as $column) {
            $this->assertTrue(Schema::hasColumn('nodes', $column), "Table nodes should have column {$column}");
        }
    }
    
    /**
     * Test model fillable attributes
     */
    public function test_node_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'label',
            'description',
            'type',
            'source',
            'embedding',
            'color',
        ];
        
        $actualFillable = (new Node())->getFillable();
        
        $this->assertEquals(
            sort($expectedFillable), 
            sort($actualFillable),
            "Node model's fillable attributes do not match expected"
        );
    }
    
    /**
     * Test that the embedding field is properly cast to an array
     */
    public function test_embedding_is_cast_to_array(): void
    {
        $embeddingData = [0.1, 0.2, 0.3, 0.4, 0.5];
        
        $node = Node::factory()->create([
            'embedding' => $embeddingData
        ]);
        
        // Refresh from DB
        $node = Node::find($node->id);
        
        $this->assertIsArray($node->embedding);
        $this->assertEquals($embeddingData, $node->embedding);
    }
    
    /**
     * Test validation rules in the NodeController
     */
    public function test_node_validation_rules(): void
    {
        // Authenticate as a user
        Passport::actingAs($this->user);
        
        // Missing required fields
        $this->postJson('/api/nodes', [])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['label', 'type']);
        
        // Label too long
        $this->postJson('/api/nodes', [
            'label' => str_repeat('a', 256), // One character too long
            'type' => 'Test',
        ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['label']);
        
        // Type too long
        $this->postJson('/api/nodes', [
            'label' => 'Test Node',
            'type' => str_repeat('a', 101), // One character too long
        ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['type']);
        
        // Source too long
        $this->postJson('/api/nodes', [
            'label' => 'Test Node',
            'type' => 'Test',
            'source' => str_repeat('a', 256), // One character too long
        ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['source']);
        
        // Color too long
        $this->postJson('/api/nodes', [
            'label' => 'Test Node',
            'type' => 'Test',
            'color' => str_repeat('a', 51), // One character too long
        ])
             ->assertStatus(422)
             ->assertJsonValidationErrors(['color']);
    }
    
    /**
     * Test that the node model can be searched
     */
    public function test_node_model_is_searchable(): void
    {
        $this->assertTrue(method_exists(Node::class, 'toSearchableArray'));
        $this->assertTrue(method_exists(Node::class, 'shouldBeSearchable'));
        
        $node = Node::factory()->create([
            'label' => 'Searchable Test Node',
            'description' => 'This node should be searchable',
        ]);
        
        $searchableArray = $node->toSearchableArray();
        
        $this->assertIsArray($searchableArray);
        $this->assertArrayHasKey('id', $searchableArray);
        $this->assertArrayHasKey('label', $searchableArray);
        $this->assertArrayHasKey('description', $searchableArray);
        $this->assertArrayHasKey('type', $searchableArray);
        $this->assertArrayHasKey('source', $searchableArray);
    }
} 