<?php
// [ai-generated-code]

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\ArticleProcessorService;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use App\Services\YoutubeTranscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContentProcessorController extends Controller
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
     * Process a content URL directly
     */
    public function process(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url'
        ]);

        if ($validator->fails()) {
            Log::warning('Content processor validation failed', [
                'errors' => $validator->errors()->toArray(),
                'ip' => $request->ip(),
                'user_id' => auth()->id() ?? 'unauthenticated',
                'timestamp' => now()->toIso8601String()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid URL',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');
            Log::info('API: Starting content processing', [
                'url' => $url,
                'ip' => $request->ip(),
                'user_id' => auth()->id() ?? 'unauthenticated',
                'timestamp' => now()->toIso8601String()
            ]);
            
            $content = $this->processorService->processFromUrl($url);
            
            Log::info('API: Content record created', [
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
            Log::info('API: Starting content extraction', [
                'content_id' => $content->id,
                'source_type' => $content->source_type,
                'start_time' => $processingStartTime
            ]);
            
            if (strtolower($content->source_type) === 'youtube') {
                $this->progressTracker->updateProgress($content, 30, 'Processing YouTube video');
                Log::info('API: Processing YouTube video', [
                    'content_id' => $content->id,
                    'url' => $content->source_url,
                    'video_id' => $this->processorService->extractYoutubeId($content->source_url)
                ]);
                
                $result = $this->youtubeProcessor->processVideo($content);
            } elseif (strtolower($content->source_type) === 'article') {
                $this->progressTracker->updateProgress($content, 30, 'Processing article');
                Log::info('API: Processing article content', [
                    'content_id' => $content->id,
                    'url' => $content->source_url
                ]);
                
                $result = $this->articleProcessor->processArticle($content);
            } else {
                $errorMessage = "Unsupported content type: {$content->source_type}";
                Log::error('API: ' . $errorMessage, [
                    'content_id' => $content->id,
                    'url' => $content->source_url
                ]);
                
                $this->progressTracker->failTracking($content, $errorMessage);
                throw new \Exception($errorMessage);
            }

            if (!$result) {
                $errorMessage = "Failed to process content: {$content->source_url}";
                Log::error('API: ' . $errorMessage, [
                    'content_id' => $content->id,
                    'source_type' => $content->source_type
                ]);
                
                $this->progressTracker->failTracking($content, $errorMessage);
                throw new \Exception($errorMessage);
            }

            $processingEndTime = microtime(true);
            $processingDuration = round($processingEndTime - $processingStartTime, 2);
            
            $this->progressTracker->updateProgress($content, 90, 'Finalizing processing');
            
            Log::info('API: Content processing completed successfully', [
                'content_id' => $content->id,
                'transcript_id' => $result->id,
                'duration_seconds' => $processingDuration,
                'token_count' => $result->token_count,
                'language' => $result->language,
                'timestamp' => now()->toIso8601String()
            ]);
            
            $this->progressTracker->completeTracking($content, 'Content processed successfully');
            
            return response()->json([
                'success' => true,
                'message' => 'Content processed successfully',
                'data' => [
                    'content_id' => $content->id,
                    'source_type' => $content->source_type,
                    'source_url' => $content->source_url,
                    'transcript_id' => $result->id,
                    'processing_time' => $processingDuration . 's'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API: Content processing failed', [
                'url' => $request->input('url'),
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 5),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process content',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get processing status for a content item
     */
    public function status(Content $content): JsonResponse
    {
        $transcript = $content->transcript;
        $progress = $this->progressTracker->getProgress($content);
        
        $statusData = [
            'content_id' => $content->id,
            'source_type' => $content->source_type,
            'source_url' => $content->source_url,
            'title' => $content->title,
            'created_at' => $content->created_at->toIso8601String(),
            'updated_at' => $content->updated_at->toIso8601String(),
            'has_transcript' => $transcript !== null
        ];
        
        if ($transcript) {
            $statusData['transcript_id'] = $transcript->id;
            $statusData['processed'] = $transcript->processed;
            
            if ($transcript->processed) {
                $statusData['status'] = 'completed';
                $statusData['progress'] = 100;
                $statusData['message'] = 'Content processing completed successfully';
            }
        }
        
        // If we have progress data, use it to override status info
        if ($progress) {
            $statusData['status'] = $progress['status'];
            $statusData['progress'] = $this->progressTracker->getProgressPercentage($content);
            $statusData['message'] = $progress['message'];
            $statusData['started_at'] = $progress['started_at'] ?? null;
            $statusData['updated_at'] = $progress['updated_at'] ?? null;
            
            if (isset($progress['completed_at'])) {
                $statusData['completed_at'] = $progress['completed_at'];
            }
            
            if (isset($progress['failed_at'])) {
                $statusData['failed_at'] = $progress['failed_at'];
            }
        
            // Default status if no progress data is available
            if (!isset($statusData['status'])) {
                $statusData['status'] = $transcript ? 'processing' : 'pending';
                $statusData['progress'] = $transcript ? 50 : 0;
                $statusData['message'] = $transcript 
                    ? 'Content is being processed' 
                    : 'Content is waiting to be processed';
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $statusData
        ]);
    }
} 