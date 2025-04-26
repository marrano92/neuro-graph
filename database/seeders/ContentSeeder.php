<?php
// [ai-generated-code]

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Node;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample content nodes
        Content::factory(15)->create();
        
        // Create some predefined content with associations to nodes
        $predefinedContents = [
            [
                'title' => 'Introduction to Neural Networks',
                'source_type' => 'Video',
                'source_url' => 'https://www.youtube.com/watch?v=example1',
                'summary' => 'This video provides a comprehensive introduction to neural networks, explaining key concepts and architectures.',
                'related_nodes' => ['Deep Learning', 'Transformers'],
            ],
            [
                'title' => 'The Role of Attention Mechanisms in Modern AI',
                'source_type' => 'Article',
                'source_url' => 'https://example.com/ai-attention-mechanisms',
                'summary' => 'This article explores how attention mechanisms have revolutionized AI, particularly in language models.',
                'related_nodes' => ['Transformers'],
            ],
            [
                'title' => 'Reinforcement Learning: Fundamentals and Applications',
                'source_type' => 'Paper',
                'source_url' => 'https://arxiv.org/abs/example123',
                'summary' => 'A comprehensive overview of reinforcement learning techniques and their applications in various domains.',
                'related_nodes' => ['Reinforcement Learning'],
            ],
            [
                'title' => 'Geoffrey Hinton on the Future of AI',
                'source_type' => 'Podcast',
                'source_url' => 'https://podcasts.example.com/geoffrey-hinton-interview',
                'summary' => 'In this interview, Geoffrey Hinton discusses his views on the future of artificial intelligence and machine learning.',
                'related_nodes' => ['Geoffrey Hinton', 'Deep Learning'],
            ],
        ];
        
        foreach ($predefinedContents as $contentData) {
            $relatedNodes = $contentData['related_nodes'];
            unset($contentData['related_nodes']);
            
            $content = Content::create($contentData);
            
            // Associate with related nodes
            foreach ($relatedNodes as $nodeLabel) {
                $node = Node::where('label', $nodeLabel)->first();
                if ($node) {
                    $content->nodes()->attach($node->id);
                }
            }
        }
    }
} 