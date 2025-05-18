<?php
// [ai-generated-code]

namespace App\Services;

use App\Models\Content;
use App\Models\Transcript;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SummaryGenerationService
{
    /**
     * Generate a summary for a content using AI
     */
    public function generateSummary(Content $content, Transcript $transcript): ?string
    {
        try {
            // Check if we have content to summarize
            if (empty($transcript->full_text)) {
                Log::warning('Cannot generate summary: transcript text is empty', [
                    'content_id' => $content->id,
                    'transcript_id' => $transcript->id
                ]);
                return null;
            }
            
            // Check OpenAI API key configuration
            $apiKey = Config::get('openai.api_key');
            if (empty($apiKey) || $apiKey === 'your-openai-api-key-here') {
                Log::error("OpenAI API key not configured", [
                    'content_id' => $content->id
                ]);
                return null;
            }
            
            // Truncate text if it's too long (OpenAI has token limits)
            $text = $this->truncateText($transcript->full_text, 4000);
            
            Log::info('Generating summary for content', [
                'content_id' => $content->id,
                'transcript_id' => $transcript->id,
                'text_length' => strlen($text)
            ]);
            
            // Generate summary using OpenAI
            $summary = $this->callOpenAI($text);
            
            if ($summary) {
                Log::info('Summary generated successfully', [
                    'content_id' => $content->id,
                    'summary_length' => strlen($summary)
                ]);
            }
            
            return $summary;
        } catch (Exception $e) {
            Log::error('Failed to generate summary: ' . $e->getMessage(), [
                'content_id' => $content->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return null;
        }
    }
    
    /**
     * Call OpenAI API to generate a summary
     */
    protected function callOpenAI(string $text): ?string
    {
        try {
            $client = app('openai');
            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that summarizes content concisely. Create a summary that captures the main points in 2-3 paragraphs.'],
                    ['role' => 'user', 'content' => "Please summarize the following text:\n\n$text"]
                ],
                'temperature' => 0.5,
                'max_tokens' => 500
            ]);
            
            return $response->choices[0]->message->content;
        } catch (Exception $e) {
            Log::error('OpenAI API error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Truncate text to a maximum character length while preserving whole sentences
     */
    protected function truncateText(string $text, int $maxChars = 4000): string
    {
        if (strlen($text) <= $maxChars) {
            return $text;
        }
        
        // Truncate to slightly less than max to account for sentence completion
        $text = substr($text, 0, $maxChars - 100);
        
        // Find the last sentence boundary
        $lastPeriod = strrpos($text, '.');
        $lastQuestion = strrpos($text, '?');
        $lastExclamation = strrpos($text, '!');
        
        $lastSentence = max($lastPeriod, $lastQuestion, $lastExclamation);
        
        if ($lastSentence !== false) {
            return substr($text, 0, $lastSentence + 1);
        }
        
        return $text;
    }
} 