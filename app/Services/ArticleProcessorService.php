<?php
// [ai-generated-code]

namespace App\Services;

use App\Models\Content;
use App\Models\Transcript;
use App\Models\TranscriptChunk;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleProcessorService
{
    /**
     * Process an article to extract its content
     */
    public function processArticle(Content $content): ?Transcript
    {
        try {
            // Extract article content
            $articleData = $this->extractArticleContent($content->source_url);
            
            if (empty($articleData['text'])) {
                throw new Exception("Failed to extract content from article: {$content->source_url}");
            }
            
            // Update content with article title
            if (!empty($articleData['title'])) {
                $content->title = $articleData['title'];
                $content->save();
            }
            
            // Create transcript from article text
            return $this->createTranscriptFromText($content, $articleData['text']);
        } catch (Exception $e) {
            Log::error("Failed to process article: " . $e->getMessage(), [
                'content_id' => $content->id,
                'url' => $content->source_url
            ]);
            
            return null;
        }
    }
    
    /**
     * Extract content from an article URL
     */
    protected function extractArticleContent(string $url): array
    {
        try {
            // This could use various methods:
            // 1. A dedicated article parsing library
            // 2. A readability service (like Mercury Parser)
            // 3. A custom extraction logic
            
            // Simple placeholder implementation that fetches HTML and extracts main content
            $response = Http::timeout(30)->get($url);
            
            if (!$response->successful()) {
                throw new Exception("Failed to fetch article: HTTP {$response->status()}");
            }
            
            $html = $response->body();
            
            // Extract title
            $title = $this->extractTitle($html);
            
            // Extract main content
            $text = $this->extractMainContent($html);
            
            return [
                'title' => $title,
                'text' => $text
            ];
        } catch (Exception $e) {
            Log::error("Failed to extract article content: " . $e->getMessage(), [
                'url' => $url
            ]);
            
            return [
                'title' => null,
                'text' => null
            ];
        }
    }
    
    /**
     * Extract title from HTML
     */
    protected function extractTitle(string $html): ?string
    {
        // Simple title extraction - in a real app would use a more robust method
        if (preg_match('/<title>(.*?)<\/title>/is', $html, $matches)) {
            return trim($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Extract main content from HTML
     */
    protected function extractMainContent(string $html): ?string
    {
        // This is a very basic implementation
        // A real app would use a more sophisticated approach like a readability algorithm
        
        // Remove script, style tags and comments
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<!--(.*?)-->/is', '', $html);
        
        // Strip all tags but keep their content
        $text = strip_tags($html);
        
        // Convert entities to characters
        $text = html_entity_decode($text);
        
        // Remove excess whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Create transcript from text content
     */
    protected function createTranscriptFromText(Content $content, string $text): Transcript
    {
        // Create transcript record
        $transcript = new Transcript();
        $transcript->content_id = $content->id;
        $transcript->full_text = $text;
        $transcript->language = $this->detectLanguage($text);
        $transcript->source_url = $content->source_url;
        $transcript->metadata = [
            'source_type' => 'article',
            'processed_at' => now()->toIso8601String()
        ];
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
            $transcriptChunk->text = $chunk;
            $transcriptChunk->chunk_index = $index;
            $transcriptChunk->token_count = $this->countTokens($chunk);
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
        
        // Group paragraphs into chunks of appropriate size
        $currentChunk = '';
        $maxChunkSize = 1000; // Approximate max tokens per chunk
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) continue;
            
            $estimatedTokens = $this->countTokens($currentChunk . "\n\n" . $paragraph);
            
            if ($estimatedTokens > $maxChunkSize && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = $paragraph;
            } else {
                $currentChunk = empty($currentChunk) ? $paragraph : $currentChunk . "\n\n" . $paragraph;
            }
        }
        
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
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