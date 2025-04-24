<?php
// [ai-generated-code]
namespace App\Console\Commands;

use App\Contracts\GraphTransformer;
use App\Discovery\TransformerStructureScout;
use Illuminate\Console\Command;

class DiscoverTransformersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discover:transformers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover GraphTransformer implementations using structure discoverer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Discovering GraphTransformer implementations...');
        
        $transformers = TransformerStructureScout::create()->get();
        
        if (empty($transformers)) {
            $this->warn('No GraphTransformer implementations found.');
            return 0;
        }
        
        $this->info('Found the following GraphTransformer implementations:');
        foreach ($transformers as $transformer) {
            $this->line("- {$transformer}");
        }
        
        // Demonstrate using the discovered transformers
        $this->info("\nDemonstrating transformation with discovered classes:");
        
        $sampleData = [
            'nodes' => [
                ['id' => 1, 'name' => 'Node 1', 'type' => 'concept', 'content' => 'This is node 1'],
                ['id' => 2, 'name' => 'Node 2', 'type' => 'concept', 'content' => 'This is node 2'],
            ],
            'connections' => [
                ['source_id' => 1, 'target_id' => 2, 'type' => 'related', 'weight' => 0.75],
            ]
        ];
        
        foreach ($transformers as $transformerClass) {
            /** @var GraphTransformer $instance */
            $instance = app()->make($transformerClass);
            $result = $instance->transform($sampleData);
            
            $this->line("\nTransformation using {$transformerClass}:");
            $this->line("Format: {$result['format']}");
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        }
        
        return 0;
    }
} 