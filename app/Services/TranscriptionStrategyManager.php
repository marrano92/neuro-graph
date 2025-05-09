<?php
// [ai-generated-code]

namespace App\Services;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\TranscriptionStrategies\TranscriptionStrategyInterface;
use Illuminate\Support\Facades\Log;

class TranscriptionStrategyManager
{
    /**
     * @var TranscriptionStrategyInterface[]
     */
    private array $strategies = [];
    
    /**
     * @var ContentProcessorService
     */
    private ContentProcessorService $contentProcessor;
    
    /**
     * Register a new transcription strategy
     */
    public function addStrategy(TranscriptionStrategyInterface $strategy): self
    {
        $this->strategies[] = $strategy;
        return $this;
    }
    
    /**
     * Set the content processor service
     */
    public function setContentProcessor(ContentProcessorService $processor): self
    {
        $this->contentProcessor = $processor;
        return $this;
    }
    
    /**
     * Process content with the appropriate strategy
     */
    public function processContent(Content $content): ?Transcript
    {
        try {
            // Extract the source ID (e.g., YouTube video ID)
            $sourceId = $this->contentProcessor->extractYoutubeId($content->source_url);
            
            if (!$sourceId) {
                Log::error("Could not extract source ID from URL", [
                    'url' => $content->source_url
                ]);
                return null;
            }
            
            // Find a suitable strategy
            foreach ($this->strategies as $strategy) {
                if ($strategy->canHandle($content)) {
                    $transcript = $strategy->transcribe($content, $sourceId);
                    
                    if ($transcript) {
                        return $transcript;
                    }
                }
            }
            
            Log::warning("No strategy succeeded in transcribing content", [
                'content_id' => $content->id,
                'url' => $content->source_url
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error("Error in transcription process: " . $e->getMessage(), [
                'content_id' => $content->id
            ]);
            
            return null;
        }
    }
} 