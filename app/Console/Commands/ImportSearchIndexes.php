<?php
// [ai-generated-code]
namespace App\Console\Commands;

use App\Models\Node;
use App\Models\User;
use Illuminate\Console\Command;

class ImportSearchIndexes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:import-all {--model= : Specific model to import}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import all models to search indexes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $model = $this->option('model');
        
        if ($model) {
            return $this->importSpecificModel($model);
        }
        
        $this->info('Importing all models to search indexes...');
        
        $this->importUsers();
        $this->importNodes();
        
        $this->info('All models imported successfully!');
        
        return Command::SUCCESS;
    }
    
    /**
     * Import specific model based on name
     */
    protected function importSpecificModel(string $model): int
    {
        $this->info("Importing {$model} to search index...");
        
        switch (strtolower($model)) {
            case 'user':
            case 'users':
                $this->importUsers();
                break;
            case 'node':
            case 'nodes':
                $this->importNodes();
                break;
            default:
                $this->error("Unknown model: {$model}");
                return Command::FAILURE;
        }
        
        $this->info("{$model} imported successfully!");
        return Command::SUCCESS;
    }
    
    /**
     * Import users to search index
     */
    protected function importUsers(): void
    {
        $this->info('Importing users...');
        User::all()->searchable();
        $this->info('Users imported!');
    }
    
    /**
     * Import nodes to search index
     */
    protected function importNodes(): void
    {
        $this->info('Importing nodes...');
        Node::all()->searchable();
        $this->info('Nodes imported!');
    }
} 