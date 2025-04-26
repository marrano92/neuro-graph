<?php
// [ai-generated-code]

namespace Database\Seeders;

use App\Models\Node;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample nodes
        Node::factory(20)->create();
        
        // Create some predefined nodes for key concepts
        $predefinedNodes = [
            [
                'label' => 'Deep Learning',
                'description' => 'A subset of machine learning involving neural networks with multiple layers.',
                'type' => 'Technology',
                'source' => 'Academic Paper',
                'color' => '#4285F4',
            ],
            [
                'label' => 'Transformers',
                'description' => 'Neural network architecture that uses self-attention mechanisms, particularly effective for NLP tasks.',
                'type' => 'Technology',
                'source' => 'Academic Paper',
                'color' => '#EA4335',
            ],
            [
                'label' => 'Reinforcement Learning',
                'description' => 'Training agents to take actions in an environment to maximize cumulative reward.',
                'type' => 'Method',
                'source' => 'Book',
                'color' => '#FBBC05',
            ],
            [
                'label' => 'Geoffrey Hinton',
                'description' => 'Computer scientist and cognitive psychologist known for his work on artificial neural networks.',
                'type' => 'Person',
                'source' => 'YouTube Video',
                'color' => '#34A853',
            ],
        ];
        
        foreach ($predefinedNodes as $node) {
            Node::create($node);
        }
    }
} 