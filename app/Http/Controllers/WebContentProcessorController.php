<?php
// [ai-generated-code]

namespace App\Http\Controllers;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use App\Services\ArticleProcessorService;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use App\Services\YoutubeTranscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebContentProcessorController extends Controller
{
    protected ContentProcessorService $processorService;
    protected ContentProcessingProgressTracker $progressTracker;
    protected YoutubeTranscriptionService $youtubeProcessor;
    protected ArticleProcessorService $articleProcessor;

    public function __construct(
        ContentProcessorService $processorService,
        ContentProcessingProgressTracker $progressTracker,
        YoutubeTranscriptionService $youtubeProcessor,
        ArticleProcessorService $articleProcessor
    ) {
        $this->processorService = $processorService;
        $this->progressTracker = $progressTracker;
        $this->youtubeProcessor = $youtubeProcessor;
        $this->articleProcessor = $articleProcessor;
    }
    
    /**
     * Show the content processor form
     */
    public function index()
    {
        return view('content-processor.index');
    }
    
    /**
     * Process a new content URL
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url'
        ]);
        
        try {
            $url = $validated['url'];
            Log::info('Starting content processing', [
                'url' => $url,
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
                'timestamp' => now()->toIso8601String()
            ]);
            
            $content = $this->processorService->processFromUrl($url);
            
            Log::info('Content record created', [
                'content_id' => $content->id,
                'source_type' => $content->source_type,
                'timestamp' => now()->toIso8601String()
            ]);
            
            // Initialize progress tracking
            $this->progressTracker->startTracking($content);
            $this->progressTracker->updateProgress($content, 10, 'Analyzing content source');
            
            // Process based on content type
            $result = null;
            
            $this->progressTracker->updateProgress($content, 20, 'Extracting content');
            
            $processingStartTime = microtime(true);
            Log::info('Starting content extraction', [
                'content_id' => $content->id,
                'source_type' => $content->source_type,
                'start_time' => $processingStartTime
            ]);
            
            if (strtolower($content->source_type) === 'youtube') {
                $this->progressTracker->updateProgress($content, 30, 'Processing YouTube video');
                Log::info('Processing YouTube video', [
                    'content_id' => $content->id,
                    'url' => $content->source_url,
                    'video_id' => $this->processorService->extractYoutubeId($content->source_url)
                ]);
                
                $result = $this->youtubeProcessor->processVideo($content);
            } elseif (strtolower($content->source_type) === 'article') {
                $this->progressTracker->updateProgress($content, 30, 'Processing article');
                Log::info('Processing article content', [
                    'content_id' => $content->id,
                    'url' => $content->source_url
                ]);
                
                $result = $this->articleProcessor->processArticle($content);
            } else {
                $errorMessage = "Unsupported content type: {$content->source_type}";
                Log::error($errorMessage, [
                    'content_id' => $content->id,
                    'url' => $content->source_url
                ]);
                
                $this->progressTracker->failTracking($content, $errorMessage);
                throw new \Exception($errorMessage);
            }

            if (!$result) {
                $errorMessage = "Failed to process content: {$content->source_url}";
                Log::error($errorMessage, [
                    'content_id' => $content->id,
                    'source_type' => $content->source_type
                ]);
                
                $this->progressTracker->failTracking($content, $errorMessage);
                throw new \Exception($errorMessage);
            }

            $processingEndTime = microtime(true);
            $processingDuration = round($processingEndTime - $processingStartTime, 2);
            
            $this->progressTracker->updateProgress($content, 90, 'Finalizing processing');
            
            Log::info('Content processing completed successfully', [
                'content_id' => $content->id,
                'transcript_id' => $result->id,
                'duration_seconds' => $processingDuration,
                'token_count' => $result->token_count,
                'language' => $result->language,
                'timestamp' => now()->toIso8601String()
            ]);
            
            $this->progressTracker->completeTracking($content, 'Content processed successfully');
            
            return redirect()
                ->route('content.processor.status', ['content' => $content->id])
                ->with('success', 'Content has been processed successfully');
        } catch (\Exception $e) {
            Log::error('Content processing failed', [
                'url' => $validated['url'] ?? 'unknown',
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 5),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to process content: ' . $e->getMessage());
        }
    }
    
    /**
     * Show the status of a content item
     */
    public function status(Content $content)
    {
        $transcript = $content->transcript;
        $progress = $this->progressTracker->getProgress($content);
        
        return view('content-processor.status', [
            'content' => $content,
            'transcript' => $transcript,
            'progress' => $progress,
            'percentage' => $this->progressTracker->getProgressPercentage($content)
        ]);
    }
    
    /**
     * List all content items
     */
    public function list(Request $request)
    {
        $contents = Content::with('transcript')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('content-processor.list', [
            'contents' => $contents
        ]);
    }
} 