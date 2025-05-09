<?php
// [ai-generated-code]

namespace App\Console\Commands;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestContentProcessingQueueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-content-processing-queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test if jobs are dispatched and processed correctly in content-processing queue';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing content-processing queue...');

        try {
            // First check if we have any content in the database to use for testing
            $this->info('Looking for an existing content record to use for testing...');
            $content = Content::first();

            if (!$content) {
                $this->warn('No existing content found. Creating a test content record...');
                $content = new Content();
                $content->title = 'Test Content';
                $content->source_url = 'https://example.com/test';
                $content->source_type = 'article';
                $content->save();
                $this->info('Created test content with ID: ' . $content->id);
            } else {
                $this->info('Using existing content with ID: ' . $content->id);
            }

            // Dispatch the job to the content-processing queue
            $this->info('Dispatching ProcessContentJob to content-processing queue...');
            ProcessContentJob::dispatch($content)->onQueue('content-processing');
            
            $this->info('Job dispatched successfully.');
            $this->info('You can check Horizon dashboard to see if the job is being processed.');
            $this->info('Or run "php artisan horizon:list" to see the status of the queued jobs.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error testing content-processing queue: ' . $e->getMessage());
            Log::error('Error testing content-processing queue: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return Command::FAILURE;
        }
    }
} 