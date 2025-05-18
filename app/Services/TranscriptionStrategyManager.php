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
        Log::debug('Transcription strategy registered', [
            'strategy' => get_class($strategy)
        ]);
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
            Log::info('Starting transcription strategy selection', [
                'content_id' => $content->id,
                'source_type' => $content->source_type,
                'url' => $content->source_url,
                'available_strategies' => count($this->strategies)
            ]);
            
            // Extract the source ID (e.g., YouTube video ID)
            $sourceId = $this->contentProcessor->extractYoutubeId($content->source_url);
            
            if (!$sourceId) {
                Log::error('Failed to extract source ID from URL', [
                    'content_id' => $content->id,
                    'url' => $content->source_url,
                    'timestamp' => now()->toIso8601String()
                ]);
                return null;
            }
            
            Log::info('Source ID extracted successfully', [
                'content_id' => $content->id,
                'source_id' => $sourceId,
                'source_type' => $content->source_type
            ]);
            
            // Find a suitable strategy
            $attemptedStrategies = [];
            
            foreach ($this->strategies as $index => $strategy) {
                $strategyName = get_class($strategy);
                
                Log::info('Trying transcription strategy', [
                    'content_id' => $content->id,
                    'strategy' => $strategyName,
                    'strategy_index' => $index + 1,
                    'total_strategies' => count($this->strategies)
                ]);
                
                $canHandle = $strategy->canHandle($content);
                
                if (!$canHandle) {
                    Log::info('Strategy cannot handle this content', [
                        'content_id' => $content->id,
                        'strategy' => $strategyName,
                        'source_type' => $content->source_type
                    ]);
                    
                    $attemptedStrategies[] = [
                        'strategy' => $strategyName,
                        'can_handle' => false,
                        'result' => null
                    ];
                    
                    continue;
                }
                
                Log::info('Strategy can handle this content, attempting transcription', [
                    'content_id' => $content->id,
                    'strategy' => $strategyName,
                    'source_id' => $sourceId,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                $startTime = microtime(true);
                $transcript = $strategy->transcribe($content, $sourceId);
                $endTime = microtime(true);
                
                $duration = round($endTime - $startTime, 2);
                
                $attemptedStrategies[] = [
                    'strategy' => $strategyName,
                    'can_handle' => true,
                    'duration' => $duration,
                    'result' => $transcript ? 'success' : 'failed'
                ];
                
                if ($transcript) {
                    Log::info('Transcription strategy succeeded', [
                        'content_id' => $content->id,
                        'strategy' => $strategyName,
                        'transcript_id' => $transcript->id,
                        'duration_seconds' => $duration,
                        'timestamp' => now()->toIso8601String()
                    ]);
                    
                    return $transcript;
                }
                
                Log::warning('Transcription strategy failed, trying next strategy', [
                    'content_id' => $content->id,
                    'strategy' => $strategyName,
                    'duration_seconds' => $duration
                ]);
            }
            
            Log::warning('All transcription strategies failed', [
                'content_id' => $content->id,
                'url' => $content->source_url,
                'attempted_strategies' => $attemptedStrategies,
                'timestamp' => now()->toIso8601String()
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Exception in transcription process', [
                'content_id' => $content->id,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_slice($e->getTrace(), 0, 3),
                'timestamp' => now()->toIso8601String()
            ]);
            
            return null;
        }
    }
} 