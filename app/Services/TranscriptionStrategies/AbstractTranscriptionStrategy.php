<?php
// [ai-generated-code]

namespace App\Services\TranscriptionStrategies;

use App\Models\Content;
use App\Models\Transcript;
use App\Models\TranscriptChunk;
use Illuminate\Support\Facades\Log;
use Exception;

abstract class AbstractTranscriptionStrategy implements TranscriptionStrategyInterface
{
    /**
     * Create transcript from text content
     */
    protected function createTranscriptFromText(Content $content, string $text, array $metadata = []): Transcript
    {
        // Create transcript record
        $transcript = new Transcript();
        $transcript->content_id = $content->id;
        $transcript->full_text = $text;
        $transcript->language = $this->detectLanguage($text);
        $transcript->source_url = $content->source_url;
        $transcript->metadata = array_merge([
            'processed_at' => now()->toIso8601String()
        ], $metadata);
        $transcript->save();
        
        // Create chunks
        $this->createTranscriptChunks($transcript, $text);
        
        // Mark as processed
        $transcript->processed = true;
        $transcript->token_count = $this->countTokens($text);
        $transcript->save();
        
        return $transcript;
    }
    
    /**
     * Create transcript chunks from full text
     */
    protected function createTranscriptChunks(Transcript $transcript, string $text): void
    {
        // Simple chunking by paragraphs or fixed length
        $chunks = $this->chunkText($text);
        
        foreach ($chunks as $index => $chunk) {
            $transcriptChunk = new TranscriptChunk();
            $transcriptChunk->transcript_id = $transcript->id;
            $transcriptChunk->text = $chunk['text'];
            $transcriptChunk->chunk_index = $index;
            $transcriptChunk->start_time = $chunk['start_time'] ?? null;
            $transcriptChunk->end_time = $chunk['end_time'] ?? null;
            $transcriptChunk->token_count = $this->countTokens($chunk['text']);
            $transcriptChunk->save();
        }
    }
    
    /**
     * Chunk text into manageable pieces
     */
    protected function chunkText(string $text): array
    {
        $chunks = [];
        $paragraphs = preg_split('/\n\s*\n/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($paragraphs as $index => $paragraph) {
            $chunks[] = [
                'text' => trim($paragraph),
                'start_time' => null,
                'end_time' => null
            ];
        }
        
        return $chunks;
    }
    
    /**
     * Count tokens in text (approximate)
     */
    protected function countTokens(string $text): int
    {
        // Simple approximation: 1 token ≈ 4 characters for English
        return (int) ceil(mb_strlen($text) / 4);
    }
    
    /**
     * Detect language of text
     */
    protected function detectLanguage(string $text): string
    {
        // Simple implementation - in a real app would use a language detection service
        return 'en';
    }
} 