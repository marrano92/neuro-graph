<?php

namespace Database\Seeders;

use App\Models\Node;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ResetAndCreateAdminSeeder::class,
        ]);

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
