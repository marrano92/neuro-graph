<?php
// [ai-generated-code]

namespace App\Console\Commands;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessContentQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:process {id?} {--all : Process all unprocessed content}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process content in the queue or a specific content item';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('all')) {
            return $this->processAllUnprocessed();
        }

        $contentId = $this->argument('id');
        if (!$contentId) {
            $this->error('Please provide a content ID or use --all option');
            return 1;
        }

        return $this->processContent($contentId);
    }

    /**
     * Process a specific content item
     */
    protected function processContent(int $contentId): int
    {
        $content = Content::find($contentId);
        
        if (!$content) {
            $this->error("Content with ID {$contentId} not found");
            return 1;
        }

        $this->info("Dispatching job to process content: {$content->id}");
        
        try {
            ProcessContentJob::dispatch($content)->onQueue('content-processing');
            $this->info("Job dispatched successfully");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to dispatch job: " . $e->getMessage());
            Log::error("Failed to dispatch content processing job", [
                'content_id' => $contentId,
                'error' => $e->getMessage()
            ]);
            return 1;
        }
    }

    /**
     * Process all unprocessed content
     */
    protected function processAllUnprocessed(): int
    {
        $query = Content::whereDoesntHave('transcript', function ($query) {
            $query->where('processed', true);
        });
        
        $count = $query->count();
        
        if ($count === 0) {
            $this->info("No unprocessed content found");
            return 0;
        }
        
        $this->info("Found {$count} unprocessed content items");
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        $query->chunkById(10, function ($contents) use ($bar) {
            foreach ($contents as $content) {
                try {
                    ProcessContentJob::dispatch($content)->onQueue('content-processing');
                } catch (\Exception $e) {
                    Log::error("Failed to dispatch content processing job", [
                        'content_id' => $content->id,
                        'error' => $e->getMessage()
                    ]);
                }
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine();
        $this->info("Jobs dispatched successfully");
        
        return 0;
    }
} 