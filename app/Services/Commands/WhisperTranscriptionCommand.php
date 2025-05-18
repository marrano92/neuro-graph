<?php
// [ai-refactored-code]

namespace App\Services\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class WhisperTranscriptionCommand
{
    /**
     * Process audio file with OpenAI Whisper API
     */
    public function execute(string $audioPath, string $model = 'whisper-1'): ?string
    {
        try {
            // Check OpenAI API key configuration
            $apiKey = Config::get('openai.api_key');
            if (empty($apiKey) || $apiKey === 'your-openai-api-key-here') {
                Log::error("OpenAI API key not configured", [
                    'audio_path' => $audioPath
                ]);
                return null;
            }
            
            // Verify file exists
            if (!file_exists($audioPath)) {
                throw new Exception("Audio file not found: $audioPath");
            }
            
            Log::info("Attempting to transcribe audio file with OpenAI Whisper", [
                'audio_path' => $audioPath,
                'file_size' => filesize($audioPath),
                'model' => $model
            ]);
            
            // Check if OpenAI client is available
            if (!class_exists('OpenAI\Laravel\Facades\OpenAI')) {
                throw new Exception("OpenAI Laravel package not installed. Run: composer require openai-php/laravel");
            }
            
            // To use the OpenAI client without using the facade
            $client = app('openai');
            $response = $client->audio()->transcribe([
                'model' => $model,
                'file' => fopen($audioPath, 'r'),
                'response_format' => 'text'
            ]);
            
            Log::info("Whisper transcription successful", [
                'audio_path' => $audioPath,
                'text_length' => strlen($response->text ?? '')
            ]);
            
            return $response->text;
        } catch (Exception $e) {
            Log::error("Whisper API error: " . $e->getMessage(), [
                'audio_path' => $audioPath,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return null;
        } finally {
            // Clean up the audio file if it exists
            if (file_exists($audioPath)) {
                unlink($audioPath);
            }
        }
    }
} 
