<?php
// [ai-generated-code]

namespace App\Services\TranscriptionStrategies;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\Commands\DownloadAudioCommand;
use App\Services\Commands\FetchVideoDetailsCommand;
use App\Services\Commands\WhisperTranscriptionCommand;
use Exception;
use Illuminate\Support\Facades\Log;

class VimeoStrategy extends AbstractTranscriptionStrategy
{
    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(Content $content): bool
    {
        // Check if the URL is a Vimeo URL
        $vimeoPattern = '/^(https?:\/\/)?(www\.)?(vimeo\.com)\/.+$/i';
        return preg_match($vimeoPattern, $content->source_url) === 1;
    }
    
    /**
     * Process the content source and generate a transcript
     */
    public function transcribe(Content $content, string $sourceId): ?Transcript
    {
        try {
            // Extract Vimeo ID from URL if needed
            $vimeoId = $this->extractVimeoId($content->source_url);
            if (!$vimeoId) {
                $vimeoId = $sourceId; // Use the provided ID as fallback
            }
            
            // 1. Download audio
            $downloadCommand = new DownloadAudioCommand();
            $audioPath = $downloadCommand->execute($vimeoId, 'vimeo');
            
            if (!$audioPath) {
                throw new Exception("Failed to download audio for Vimeo ID: $vimeoId");
            }
            
            // 2. Transcribe with Whisper
            $whisperCommand = new WhisperTranscriptionCommand();
            $transcriptionText = $whisperCommand->execute($audioPath);
            
            if (empty($transcriptionText)) {
                return null;
            }
            
            // 3. Get video details
            $fetchDetailsCommand = new FetchVideoDetailsCommand();
            $videoDetails = $fetchDetailsCommand->execute($vimeoId, 'vimeo');
            
            // 4. Create transcript
            return $this->createTranscriptFromText($content, $transcriptionText, [
                'source_type' => 'vimeo_whisper',
                'model' => 'whisper-1',
                'video_title' => $videoDetails['title'] ?? null,
                'video_duration' => $videoDetails['duration'] ?? null,
                'video_author' => $videoDetails['author'] ?? null
            ]);
        } catch (Exception $e) {
            Log::error("Vimeo transcription failed: " . $e->getMessage(), [
                'content_id' => $content->id,
                'vimeo_id' => $sourceId
            ]);
            
            return null;
        }
    }
    
    /**
     * Extract Vimeo ID from URL
     */
    private function extractVimeoId(string $url): ?string
    {
        // Extract Vimeo ID from URL
        // Example: https://vimeo.com/123456789 => 123456789
        if (preg_match('/vimeo\.com\/(\d+)/', $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
} 