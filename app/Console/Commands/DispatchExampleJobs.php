<?php
// [ai-generated-code]
namespace App\Console\Commands;

use App\Jobs\ExampleJob;
use Illuminate\Console\Command;

class DispatchExampleJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:dispatch {count=10 : Number of jobs to dispatch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch example jobs to demonstrate Horizon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        
        $this->info("Dispatching {$count} example jobs...");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        for ($i = 1; $i <= $count; $i++) {
            ExampleJob::dispatch("Job #{$i} at " . now()->toDateTimeString());
            $bar->advance();
            
            // Small delay between dispatches for better visualization
            if ($i < $count) {
                usleep(200000); // 200ms
            }
        }
        
        $bar->finish();
        $this->newLine();
        $this->info("Successfully dispatched {$count} jobs. Check Horizon dashboard to monitor processing.");
        
        return Command::SUCCESS;
    }
} 