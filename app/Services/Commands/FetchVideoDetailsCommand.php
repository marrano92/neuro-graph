<?php
// [ai-generated-code]

namespace App\Services\Commands;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FetchVideoDetailsCommand
{
    /**
     * Fetch video details from YouTube or other platforms
     */
    public function execute(string $videoId, string $platform = 'youtube'): array
    {
        try {
            if ($platform === 'youtube') {
                return $this->fetchYoutubeDetails($videoId);
            } elseif ($platform === 'vimeo') {
                return $this->fetchVimeoDetails($videoId);
            } else {
                throw new Exception("Unsupported platform: $platform");
            }
        } catch (Exception $e) {
            Log::warning("Failed to get video details: " . $e->getMessage(), [
                'video_id' => $videoId,
                'platform' => $platform
            ]);
            
            return [
                'title' => "Video $videoId",
                'duration' => 0
            ];
        }
    }
    
    /**
     * Fetch YouTube video details using yt-dlp
     */
    private function fetchYoutubeDetails(string $videoId): array
    {
        if (!$this->isYtDlpInstalled()) {
            return $this->fallbackYoutubeDetails($videoId);
        }
        
        $url = "https://www.youtube.com/watch?v=$videoId";
        $process = new Process([
            'yt-dlp',
            '--dump-json',
            '--skip-download',
            $url
        ]);
        
        $process->setTimeout(30);
        $process->run();
        
        if (!$process->isSuccessful()) {
            Log::error('Failed to fetch YouTube video details', [
                'youtube_id' => $videoId,
                'error' => $process->getErrorOutput()
            ]);
            return $this->fallbackYoutubeDetails($videoId);
        }
        
        $output = $process->getOutput();
        $data = json_decode($output, true);
        
        if (empty($data) || !is_array($data)) {
            Log::error('Invalid JSON data from yt-dlp', [
                'youtube_id' => $videoId,
                'output' => substr($output, 0, 200) . '...'
            ]);
            return $this->fallbackYoutubeDetails($videoId);
        }
        
        return [
            'title' => $data['title'] ?? "YouTube Video $videoId",
            'duration' => $data['duration'] ?? 0,
            'author' => $data['uploader'] ?? null,
            'upload_date' => $data['upload_date'] ?? null,
            'view_count' => $data['view_count'] ?? 0,
            'description' => $data['description'] ?? null,
            'platform' => 'youtube',
        ];
    }
    
    /**
     * Fallback YouTube details when yt-dlp is not available or fails
     */
    private function fallbackYoutubeDetails(string $videoId): array
    {
        // This is a placeholder for YouTube Data API integration
        // In a real implementation, would call YouTube API with proper token
        
        return [
            'title' => "YouTube Video $videoId",
            'duration' => 0,
            'platform' => 'youtube'
        ];
    }
    
    /**
     * Fetch Vimeo video details
     */
    private function fetchVimeoDetails(string $videoId): array
    {
        // Try to use yt-dlp first
        if ($this->isYtDlpInstalled()) {
            $url = "https://vimeo.com/$videoId";
            $process = new Process([
                'yt-dlp',
                '--dump-json',
                '--skip-download',
                $url
            ]);
            
            $process->setTimeout(30);
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $data = json_decode($output, true);
                
                if (!empty($data) && is_array($data)) {
                    return [
                        'title' => $data['title'] ?? "Vimeo Video $videoId",
                        'duration' => $data['duration'] ?? 0,
                        'author' => $data['uploader'] ?? null,
                        'upload_date' => $data['upload_date'] ?? null,
                        'platform' => 'vimeo',
                    ];
                }
            }
        }
        
        // Fallback to placeholder
        return [
            'title' => "Vimeo Video $videoId",
            'duration' => 0,
            'platform' => 'vimeo'
        ];
    }
    
    /**
     * Check if yt-dlp is installed
     */
    private function isYtDlpInstalled(): bool
    {
        $checkProcess = new Process(['which', 'yt-dlp']);
        $checkProcess->run();
        
        if (!$checkProcess->isSuccessful()) {
            Log::error('yt-dlp is not installed in the container', [
                'error' => 'dependency missing',
                'dependency' => 'yt-dlp'
            ]);
            return false;
        }
        
        return true;
    }
} 