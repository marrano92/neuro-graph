<?php
// [ai-generated-code]

namespace App\Services\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DownloadAudioCommand
{
    /**
     * Download audio from a YouTube video or other supported sources
     */
    public function execute(string $videoId, string $source = 'youtube'): ?string
    {
        try {
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            $outputPath = "$tempDir/$videoId.mp3";
            
            $sourceUrl = $this->getSourceUrl($videoId, $source);
            
            $command = "yt-dlp -x --audio-format mp3 --audio-quality 0 -o " . 
                       escapeshellarg($outputPath) . " " . 
                       escapeshellarg($sourceUrl);
                       
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0 || !file_exists($outputPath)) {
                throw new Exception("Failed to download audio: " . implode("\n", $output));
            }
            
            return $outputPath;
        } catch (Exception $e) {
            Log::error("Failed to download audio: " . $e->getMessage(), [
                'video_id' => $videoId,
                'source' => $source
            ]);
            
            return null;
        }
    }
    
    /**
     * Get the source URL based on platform
     */
    private function getSourceUrl(string $videoId, string $source): string
    {
        if ($source === 'youtube') {
            return "https://www.youtube.com/watch?v=$videoId";
        } elseif ($source === 'vimeo') {
            return "https://vimeo.com/$videoId";
        } else {
            throw new Exception("Unsupported source: $source");
        }
    }
} 