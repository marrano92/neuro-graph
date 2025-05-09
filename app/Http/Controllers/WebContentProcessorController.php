<?php
// [ai-generated-code]

namespace App\Http\Controllers;

use App\Jobs\ProcessContentJob;
use App\Models\Content;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebContentProcessorController extends Controller
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
            $content = $this->processorService->processFromUrl($url);
            
            // Initialize progress tracking
            $this->progressTracker->startTracking($content);
            
            // Queue the content for processing
            ProcessContentJob::dispatch($content)->onQueue('content-processing');
            
            return redirect()
                ->route('content.processor.status', ['content' => $content->id])
                ->with('success', 'Content has been queued for processing');
        } catch (\Exception $e) {
            Log::error("Failed to process content: " . $e->getMessage(), [
                'url' => $url
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