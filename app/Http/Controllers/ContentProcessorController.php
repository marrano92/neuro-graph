<?php
// [ai-generated-code]

namespace App\Http\Controllers;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContentProcessorController extends Controller
{
    protected ContentProcessorService $processorService;
    protected ContentProcessingProgressTracker $progressTracker;

    public function __construct(
        ContentProcessorService $processorService,
        ContentProcessingProgressTracker $progressTracker
    ) {
        $this->processorService = $processorService;
        $this->progressTracker = $progressTracker;
    }

    /**
     * Process a content URL and queue it for processing
     */
    public function process(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid URL',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');
            $content = $this->processorService->processFromUrl($url);
            
            // Initialize progress tracking
            $this->progressTracker->startTracking($content);
            
            // Queue the content for processing
            ProcessContentJob::dispatch($content)->onQueue('content-processing');
            
            return response()->json([
                'success' => true,
                'message' => 'Content queued for processing',
                'data' => [
                    'content_id' => $content->id,
                    'source_type' => $content->source_type,
                    'source_url' => $content->source_url
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to queue content for processing: " . $e->getMessage(), [
                'url' => $request->input('url')
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
        } else {
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