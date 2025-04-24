<?php
// [ai-generated-code]
namespace App\Console\Commands;

use App\Discovery\ModelStructureScout;
use Illuminate\Console\Command;

class DiscoverModelsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discover:models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Discover models in the application using structure discoverer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Discovering models in the application...');
        
        $models = ModelStructureScout::create()->get();
        
        if (empty($models)) {
            $this->warn('No models found.');
            return 0;
        }
        
        $this->info('Found the following models:');
        foreach ($models as $model) {
            $this->line("- {$model}");
        }
        
        return 0;
    }
} 