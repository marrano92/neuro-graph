<?php
// [ai-generated-code]

namespace App\Console\Commands;

use App\Models\Node;
use App\Services\EmbeddingService;
use Illuminate\Console\Command;

class GenerateNodeEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nodes:generate-embeddings {--force : Force regeneration of all embeddings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate embeddings for nodes that don\'t have them';

    /**
     * Execute the console command.
     */
    public function handle(EmbeddingService $embeddingService)
    {
        $forceAll = $this->option('force');
        
        $query = Node::query();
        
        if (!$forceAll) {
            $query->whereNull('embedding');
        }
        
        $nodes = $query->get();
        $count = $nodes->count();
        
        if ($count === 0) {
            $this->info('No nodes found that need embeddings.');
            return 0;
        }
        
        $this->info("Generating embeddings for {$count} nodes...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        foreach ($nodes as $node) {
            // Concatenate label and description to generate the embedding
            $text = $node->label;
            if ($node->description) {
                $text .= " " . $node->description;
            }
            
            // Generate embedding
            $embedding = $embeddingService->generateEmbedding($text);
            
            // Update node
            $node->embedding = $embedding;
            $node->save();
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Embeddings generated successfully!');
        
        return 0;
    }
} 