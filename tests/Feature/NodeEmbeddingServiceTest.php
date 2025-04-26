<?php
// [ai-generated-code]

namespace Tests\Feature;

use App\Models\Node;
use App\Services\EmbeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class NodeEmbeddingServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;
    
    private EmbeddingService $embeddingService;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->embeddingService = new EmbeddingService();
    }
    
    /**
     * Test that the embedding service can generate embeddings
     */
    public function test_can_generate_embeddings(): void
    {
        $text = 'This is a test text for embedding generation';
        
        $embedding = $this->embeddingService->generateEmbedding($text);
        
        $this->assertIsArray($embedding);
        $this->assertNotEmpty($embedding);
        
        // By default, our service should generate 384-dimensional embeddings
        $this->assertCount(384, $embedding);
        
        // Embeddings should be normalized (have unit length)
        $this->assertEqualsWithDelta(1.0, $this->calculateMagnitude($embedding), 0.0001);
    }
    
    /**
     * Test generating embeddings with custom dimensions
     */
    public function test_can_generate_embeddings_with_custom_dimensions(): void
    {
        $text = 'This is a test text for embedding generation';
        $dimensions = 128;
        
        $embedding = $this->embeddingService->generateEmbedding($text, $dimensions);
        
        $this->assertIsArray($embedding);
        $this->assertCount($dimensions, $embedding);
    }
    
    /**
     * Test that the same text always generates the same embedding
     */
    public function test_embeddings_are_consistent_for_same_text(): void
    {
        $text = 'This is a consistent text that should generate the same embedding';
        
        $embedding1 = $this->embeddingService->generateEmbedding($text);
        $embedding2 = $this->embeddingService->generateEmbedding($text);
        
        $this->assertEquals($embedding1, $embedding2);
    }
    
    /**
     * Test cosine similarity calculation between vectors
     */
    public function test_cosine_similarity_calculation(): void
    {
        // Two identical vectors should have similarity 1.0
        $vector1 = [0.1, 0.2, 0.3];
        $vector2 = [0.1, 0.2, 0.3];
        
        $similarity = $this->embeddingService->cosineSimilarity($vector1, $vector2);
        $this->assertEqualsWithDelta(1.0, $similarity, 0.0001);
        
        // Perpendicular vectors should have similarity 0
        $vector1 = [1, 0, 0];
        $vector2 = [0, 1, 0];
        
        $similarity = $this->embeddingService->cosineSimilarity($vector1, $vector2);
        $this->assertEqualsWithDelta(0.0, $similarity, 0.0001);
        
        // Opposite vectors should have similarity -1
        $vector1 = [1, 2, 3];
        $vector2 = [-1, -2, -3];
        
        $similarity = $this->embeddingService->cosineSimilarity($vector1, $vector2);
        $this->assertEqualsWithDelta(-1.0, $similarity, 0.0001);
    }
    
    /**
     * Test that semantically similar texts have higher similarity scores
     * Note: This is a more subjective test and might be flaky with the current placeholder implementation
     */
    public function test_semantically_similar_texts_have_higher_similarity(): void
    {
        // Skip this test for now as it's not reliable with random embeddings
        $this->markTestSkipped(
            'Skipping semantic similarity test as the placeholder embedding service uses pseudo-random values'
        );
        
        // In a real implementation with actual semantic embeddings, we would test:
        // - Generate embeddings for semantically related texts
        // - Check that similar concepts have higher similarity scores
    }
    
    /**
     * Test node integration with embeddings
     */
    public function test_nodes_with_embeddings_can_find_similarities(): void
    {
        // Create custom embeddings for testing
        $aiEmbedding = $this->createNormalizedVector([0.8, 0.6, 0.2, 0.1, 0.0]);
        $mlEmbedding = $this->createNormalizedVector([0.7, 0.7, 0.3, 0.1, 0.0]);
        $dlEmbedding = $this->createNormalizedVector([0.6, 0.8, 0.4, 0.2, 0.0]);
        $musicEmbedding = $this->createNormalizedVector([0.1, 0.2, 0.8, 0.7, 0.6]);
        
        // Create nodes with specific concepts and controlled embeddings
        $aiNode = Node::factory()->create([
            'label' => 'Artificial Intelligence',
            'description' => 'The field of AI focuses on creating machines that can perform tasks requiring human intelligence.',
            'embedding' => $aiEmbedding
        ]);
        
        $mlNode = Node::factory()->create([
            'label' => 'Machine Learning',
            'description' => 'A subset of AI that enables computers to learn from data without explicit programming.',
            'embedding' => $mlEmbedding
        ]);
        
        $deepLearningNode = Node::factory()->create([
            'label' => 'Deep Learning',
            'description' => 'A subset of machine learning using neural networks with multiple layers.',
            'embedding' => $dlEmbedding
        ]);
        
        $musicNode = Node::factory()->create([
            'label' => 'Classical Music',
            'description' => 'A genre of music produced in the Western classical tradition.',
            'embedding' => $musicEmbedding
        ]);
        
        // Calculate similarities
        $similarity_ai_ml = $this->embeddingService->cosineSimilarity($aiNode->embedding, $mlNode->embedding);
        $similarity_ml_dl = $this->embeddingService->cosineSimilarity($mlNode->embedding, $deepLearningNode->embedding);
        $similarity_ai_music = $this->embeddingService->cosineSimilarity($aiNode->embedding, $musicNode->embedding);
        
        // Similar concepts should have higher similarity scores
        $this->assertGreaterThan(0, $similarity_ai_ml, "AI and ML should have positive similarity");
        $this->assertGreaterThan(0, $similarity_ml_dl, "ML and DL should have positive similarity");
        
        // Machine Learning should be closer to Deep Learning than AI is to Music
        $this->assertGreaterThan($similarity_ai_music, $similarity_ml_dl, 
            "ML->DL similarity should be greater than AI->Music similarity");
    }
    
    /**
     * Create a normalized vector for testing
     */
    private function createNormalizedVector(array $vector): array
    {
        $magnitude = $this->calculateMagnitude($vector);
        return array_map(function($x) use ($magnitude) {
            return $x / $magnitude;
        }, $vector);
    }
    
    /**
     * Helper function to calculate vector magnitude
     */
    private function calculateMagnitude(array $vector): float
    {
        return sqrt(array_sum(array_map(function($x) { 
            return $x * $x; 
        }, $vector)));
    }
} 