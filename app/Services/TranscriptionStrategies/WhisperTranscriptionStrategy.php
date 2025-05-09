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

class WhisperTranscriptionStrategy extends AbstractTranscriptionStrategy
{
    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(Content $content): bool
    {
        // This is a fallback strategy that can handle any media URL
        // YouTube, Vimeo, and other platforms that yt-dlp supports
        return true;
    }
    
    /**
     * Process the content source and generate a transcript
     */
    public function transcribe(Content $content, string $sourceId): ?Transcript
    {
        try {
            // Determine source platform from URL
            $platform = $this->determinePlatform($content->source_url);
            
            // 1. Download audio
            $downloadCommand = new DownloadAudioCommand();
            $audioPath = $downloadCommand->execute($sourceId, $platform);
            
            if (!$audioPath) {
                throw new Exception("Failed to download audio for source ID: $sourceId");
            }
            
            // 2. Transcribe with Whisper
            $whisperCommand = new WhisperTranscriptionCommand();
            $transcriptionText = $whisperCommand->execute($audioPath);
            
            if (empty($transcriptionText)) {
                return null;
            }
            
            // 3. Get media details
            $fetchDetailsCommand = new FetchVideoDetailsCommand();
            $mediaDetails = $fetchDetailsCommand->execute($sourceId, $platform);
            
            // Update content title if available
            if (isset($mediaDetails['title']) && !str_contains($mediaDetails['title'], $sourceId)) {
                $content->title = $mediaDetails['title'];
                $content->save();
            }
            
            // 4. Create transcript
            return $this->createTranscriptFromText($content, $transcriptionText, [
                'source_type' => $platform . '_whisper',
                'model' => 'whisper-1'
            ]);
        } catch (Exception $e) {
            Log::error("Whisper transcription failed: " . $e->getMessage(), [
                'content_id' => $content->id,
                'source_id' => $sourceId
            ]);
            
            return null;
        }
    }
    
    /**
     * Determine the platform from the URL
     */
    private function determinePlatform(string $url): string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        } elseif (str_contains($url, 'vimeo.com')) {
            return 'vimeo';
        } else {
            return 'unknown';
        }
    }
} 