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
    protected SummaryGenerationService $summaryService;
    
    public function __construct(
        ContentProcessorService $processorService, 
        TranscriptionStrategyManager $strategyManager,
        SummaryGenerationService $summaryService
    ) {
        $this->processorService = $processorService;
        $this->strategyManager = $strategyManager;
        $this->summaryService = $summaryService;
        
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
            
            // Get video details and update content title
            $this->updateContentFromVideoDetails($content, $sourceId);
            
            // Process with strategy manager
            $transcript = $this->strategyManager->processContent($content);
            
            if (!$transcript) {
                Log::warning("No transcription strategy succeeded", [
                    'content_id' => $content->id,
                    'url' => $url
                ]);
                return null;
            }
            
            // Generate and update summary
            $this->generateAndUpdateSummary($content, $transcript);
            
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
     * Update content with video details (title, etc.)
     */
    protected function updateContentFromVideoDetails(Content $content, string $sourceId): void
    {
        try {
            // Determine platform from URL
            $platform = $this->determinePlatform($content->source_url);
            
            // Fetch video details
            $fetchDetailsCommand = new Commands\FetchVideoDetailsCommand();
            $videoDetails = $fetchDetailsCommand->execute($sourceId, $platform);
            
            // Update content title if available
            if (isset($videoDetails['title']) && !str_contains($videoDetails['title'], $sourceId)) {
                $content->title = $videoDetails['title'];
                
                Log::info('Content title updated from video details', [
                    'content_id' => $content->id,
                    'title' => $videoDetails['title'],
                    'platform' => $platform
                ]);
            }
            
            // Save the updated content
            $content->save();
        } catch (Exception $e) {
            Log::warning('Failed to update content from video details: ' . $e->getMessage(), [
                'content_id' => $content->id,
                'source_id' => $sourceId
            ]);
        }
    }
    
    /**
     * Generate and update content summary
     */
    protected function generateAndUpdateSummary(Content $content, Transcript $transcript): void
    {
        try {
            $summary = $this->summaryService->generateSummary($content, $transcript);
            
            if ($summary) {
                $content->summary = $summary;
                $content->save();
                
                Log::info('Content summary updated', [
                    'content_id' => $content->id,
                    'summary_length' => strlen($summary)
                ]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to generate summary: ' . $e->getMessage(), [
                'content_id' => $content->id
            ]);
        }
    }
    
    /**
     * Determine the platform from URL
     */
    protected function determinePlatform(string $url): string
    {
        if (preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/i', $url)) {
            return 'youtube';
        } elseif (preg_match('/^(https?:\/\/)?(www\.)?(vimeo\.com)\/.+$/i', $url)) {
            return 'vimeo';
        }
        
        return 'unknown';
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