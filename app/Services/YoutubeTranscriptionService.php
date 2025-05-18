<?php
// [ai-refactored-code]

namespace App\Services;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\TranscriptionStrategies\YoutubeSubtitlesStrategy;
use App\Services\TranscriptionStrategies\WhisperTranscriptionStrategy;
use App\Services\TranscriptionStrategies\VimeoStrategy;
use Exception;
use Illuminate\Support\Facades\Log;

class YoutubeTranscriptionService
{
    protected TranscriptionStrategyManager $strategyManager;
    protected ContentProcessorService $processorService;
    
    public function __construct(
        ContentProcessorService $processorService, 
        TranscriptionStrategyManager $strategyManager
    ) {
        $this->processorService = $processorService;
        $this->strategyManager = $strategyManager;
        
        // Configure the strategy manager
        $this->strategyManager->setContentProcessor($processorService);
        
        // Register strategies in order of preference
        
        // Platform-specific strategies
        $this->strategyManager->addStrategy(new YoutubeSubtitlesStrategy());
        $this->strategyManager->addStrategy(new VimeoStrategy());
        
        // Generic fallback strategy
        $this->strategyManager->addStrategy(new WhisperTranscriptionStrategy());
    }
    
    /**
     * Process a YouTube video to extract its transcript
     */
    public function processVideo(Content $content): ?Transcript
    {
        try {
            // Get the URL from the content
            $url = $content->url ?? $content->source_url ?? null;
            
            if (!$url) {
                Log::error("Missing URL in content", [
                    'content_id' => $content->id,
                    'content' => $content->toArray()
                ]);
                throw new Exception("Missing URL in content");
            }
            
            // Extract source ID from URL (supports YouTube, Vimeo, etc.)
            $sourceId = $this->extractSourceId($url);
            
            if (!$sourceId) {
                throw new Exception("Invalid media URL: {$url}");
            }
            
            // Process with strategy manager
            $transcript = $this->strategyManager->processContent($content);
            
            if (!$transcript) {
                Log::warning("No transcription strategy succeeded", [
                    'content_id' => $content->id,
                    'url' => $url
                ]);
            }
            
            return $transcript;
        } catch (Exception $e) {
            Log::error("Failed to process media: " . $e->getMessage(), [
                'content_id' => $content->id,
                'url' => $content->url ?? $content->source_url ?? 'unknown',
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return null;
        }
    }
    
    /**
     * Extract source ID from various platforms
     */
    protected function extractSourceId(string $url): ?string
    {
        // Try YouTube
        $youtubeId = $this->processorService->extractYoutubeId($url);
        if ($youtubeId) {
            return $youtubeId;
        }
        
        // Try Vimeo
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        // Add other platforms as needed
        
        return null;
    }
} 