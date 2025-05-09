<?php
// [ai-generated-code]

namespace App\Jobs;

use App\Models\Content;
use App\Services\ArticleProcessorService;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use App\Services\YoutubeTranscriptionService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * The content to process
     */
    protected Content $content;

    /**
     * Create a new job instance.
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     */
    public function handle(
        ContentProcessorService $contentProcessor,
        YoutubeTranscriptionService $youtubeProcessor,
        ArticleProcessorService $articleProcessor,
        ContentProcessingProgressTracker $progressTracker
    ): void {
        try {
            Log::info("Processing content: {$this->content->id}", [
                'source_url' => $this->content->source_url,
                'source_type' => $this->content->source_type
            ]);
            
            // Start progress tracking
            $progressTracker->startTracking($this->content);
            $progressTracker->updateProgress($this->content, 10, 'Analyzing content source');

            // Process based on content type
            $result = null;
            
            $progressTracker->updateProgress($this->content, 20, 'Extracting content');
            
            if (strtolower($this->content->source_type) === 'youtube') {
                $progressTracker->updateProgress($this->content, 30, 'Processing YouTube video');
                $result = $youtubeProcessor->processVideo($this->content);
            } elseif (strtolower($this->content->source_type) === 'article') {
                $progressTracker->updateProgress($this->content, 30, 'Processing article');
                $result = $articleProcessor->processArticle($this->content);
            } else {
                $progressTracker->failTracking($this->content, "Unsupported content type: {$this->content->source_type}");
                throw new Exception("Unsupported content type: {$this->content->source_type}");
            }

            if (!$result) {
                $progressTracker->failTracking($this->content, "Failed to process content: {$this->content->source_url}");
                throw new Exception("Failed to process content: {$this->content->source_url}");
            }

            $progressTracker->updateProgress($this->content, 90, 'Finalizing processing');
            
            Log::info("Successfully processed content: {$this->content->id}", [
                'transcript_id' => $result->id
            ]);
            
            $progressTracker->completeTracking($this->content, 'Content processed successfully');
        } catch (Exception $e) {
            Log::error("Error processing content: " . $e->getMessage(), [
                'content_id' => $this->content->id,
                'source_url' => $this->content->source_url
            ]);
            
            // Mark tracking as failed with error message
            $progressTracker->failTracking($this->content, "Processing failed: " . $e->getMessage());

            // Rethrow to trigger job retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        Log::error("Content processing job failed: " . $exception->getMessage(), [
            'content_id' => $this->content->id,
            'source_url' => $this->content->source_url
        ]);
    }
} 