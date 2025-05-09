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
            // Extract source ID from URL (supports YouTube, Vimeo, etc.)
            $sourceId = $this->extractSourceId($content->source_url);
            
            if (!$sourceId) {
                throw new Exception("Invalid media URL: {$content->source_url}");
            }
            
            // Process with strategy manager
            $transcript = $this->strategyManager->processContent($content);
            
            if (!$transcript) {
                Log::warning("No transcription strategy succeeded", [
                    'content_id' => $content->id,
                    'url' => $content->source_url
                ]);
            }
            
            return $transcript;
        } catch (Exception $e) {
            Log::error("Failed to process media: " . $e->getMessage(), [
                'content_id' => $content->id,
                'url' => $content->source_url
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