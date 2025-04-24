<?php

namespace Database\Seeders;

use App\Models\Node;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        User::factory(10)->create();

        // Create nodes
        $nodeTypes = ['text', 'image', 'file'];
        
        for ($i = 1; $i <= 15; $i++) {
            Node::create([
                'name' => 'Node ' . $i,
                'type' => $nodeTypes[array_rand($nodeTypes)],
                'content' => 'This is the content for node ' . $i . '. It contains some searchable text that will be indexed by Meilisearch.',
            ]);
        }
        
        // Create specific nodes for better search testing
        Node::create([
            'name' => 'Meilisearch Documentation',
            'type' => 'text',
            'content' => 'Meilisearch is a powerful, fast, open-source, easy to use and deploy search engine. Both searching and indexing are highly customizable.',
        ]);
        
        Node::create([
            'name' => 'Laravel Scout Guide',
            'type' => 'text',
            'content' => 'Laravel Scout provides a simple, driver-based solution for adding full-text search to your Eloquent models with Meilisearch.',
        ]);
        
        Node::create([
            'name' => 'Search API Image',
            'type' => 'image',
            'content' => 'An image showing how to use the search API with Laravel Scout and Meilisearch integration.',
        ]);
    }
}
