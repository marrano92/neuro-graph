<?php
// [ai-generated-code]

namespace App\Services\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class WhisperTranscriptionCommand
{
    /**
     * Process audio file with OpenAI Whisper API
     */
    public function execute(string $audioPath, string $model = 'whisper-1'): ?string
    {
        try {
            if (!file_exists($audioPath)) {
                throw new Exception("Audio file not found: $audioPath");
            }
            
            $response = OpenAI::audio()->transcribe([
                'model' => $model,
                'file' => fopen($audioPath, 'r'),
                'response_format' => 'text'
            ]);
            
            return $response->text;
        } catch (Exception $e) {
            Log::error("Whisper API error: " . $e->getMessage(), [
                'audio_path' => $audioPath
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