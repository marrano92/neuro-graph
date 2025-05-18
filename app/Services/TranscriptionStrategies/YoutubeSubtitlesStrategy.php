<?php
// [ai-refactored-code]

namespace App\Services\TranscriptionStrategies;

use App\Models\Content;
use App\Models\Transcript;
use App\Services\Commands\FetchVideoDetailsCommand;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class YoutubeSubtitlesStrategy extends AbstractTranscriptionStrategy
{
    private const YOUTUBE_URL_PATTERN = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/i';
    
    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(Content $content): bool
    {
        $result = preg_match(self::YOUTUBE_URL_PATTERN, $content->source_url) === 1;
        
        Log::debug('YoutubeSubtitlesStrategy canHandle check', [
            'content_id' => $content->id,
            'url' => $content->source_url,
            'can_handle' => $result
        ]);
        
        return $result;
    }
    
    /**
     * Process the content source and generate a transcript
     */
    public function transcribe(Content $content, string $sourceId): ?Transcript
    {
        try {
            Log::info('Starting YouTube subtitles extraction', [
                'content_id' => $content->id,
                'youtube_id' => $sourceId
            ]);
            
            // Check subtitles availability
            if (!$this->checkSubtitlesAvailable($sourceId)) {
                Log::warning('No subtitles available for YouTube video', [
                    'content_id' => $content->id,
                    'youtube_id' => $sourceId
                ]);
                return null;
            }
            
            // Fetch subtitles
            $subtitles = $this->fetchSubtitles($sourceId);
            if (empty($subtitles)) {
                Log::warning('Failed to fetch subtitles for YouTube video', [
                    'content_id' => $content->id,
                    'youtube_id' => $sourceId
                ]);
                return null;
            }
            
            // Get video details
            $videoDetails = $this->getVideoDetails($sourceId);
            
            // Create transcript
            return $this->createTranscriptFromText($content, $subtitles, [
                'source_type' => 'youtube_subtitles',
                'youtube_id' => $sourceId,
                'video_title' => $videoDetails['title'] ?? null,
                'video_duration' => $videoDetails['duration'] ?? null,
                'video_author' => $videoDetails['author'] ?? null,
                'extraction_method' => 'youtube_subtitles',
                'extracted_at' => now()->toIso8601String()
            ]);
        } catch (Exception $e) {
            Log::warning('Failed to get YouTube subtitles', [
                'content_id' => $content->id,
                'youtube_id' => $sourceId,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e)
            ]);
            
            return null;
        }
    }
    
    /**
     * Check if subtitles are available for a video
     */
    private function checkSubtitlesAvailable(string $videoId): bool
    {
        try {
            if (!$this->isYtDlpInstalled()) {
                return false;
            }
            
            $process = new Process([
                'yt-dlp', 
                '--list-subs',
                '--skip-download',
                "https://www.youtube.com/watch?v=$videoId"
            ]);
            
            $process->setTimeout(60);
            $process->run();
            
            if (!$process->isSuccessful()) {
                Log::error('Failed to list subtitles', [
                    'youtube_id' => $videoId,
                    'error' => $process->getErrorOutput()
                ]);
                return false;
            }
            
            $output = $process->getOutput();
            $hasManualSubs = strpos($output, 'Available subtitles') !== false && 
                             strpos($output, 'has no subtitles') === false;
            $hasAutoSubs = strpos($output, 'Available automatic captions') !== false;
            
            return $hasManualSubs || $hasAutoSubs;
        } catch (Exception $e) {
            Log::warning('Error checking YouTube subtitles availability', [
                'youtube_id' => $videoId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
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
    
    /**
     * Get video details from YouTube
     */
    private function getVideoDetails(string $videoId): array
    {
        $fetchDetailsCommand = new FetchVideoDetailsCommand();
        return $fetchDetailsCommand->execute($videoId, 'youtube');
    }
    
    /**
     * Fetch subtitles from YouTube
     */
    private function fetchSubtitles(string $videoId): ?string
    {
        try {
            // Create temporary directory
            $tempDir = storage_path('app/temp/youtube_subtitles/' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Save current directory and change to temp dir
            $currentDir = getcwd();
            chdir($tempDir);
            
            $subtitleContent = $this->attemptSubtitleFetch($videoId, $tempDir);
            
            // Clean up and restore directory
            array_map('unlink', glob($tempDir . "/*"));
            rmdir($tempDir);
            chdir($currentDir);
            
            if (!$subtitleContent) {
                return null;
            }
            
            return $this->cleanSubtitleContent($subtitleContent);
        } catch (Exception $e) {
            Log::warning('Error fetching YouTube subtitles', [
                'youtube_id' => $videoId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Attempt to fetch subtitles using multiple methods
     */
    private function attemptSubtitleFetch(string $videoId, string $tempDir): ?string
    {
        // First attempt - standard method
        $this->runYtDlpCommand($videoId, [
            '--skip-download',
            '--write-auto-sub',
            '--sub-lang', 'en',
            '--output', 'subtitle'
        ]);
        
        $subtitleFiles = glob($tempDir . "/subtitle*.en.vtt");
        
        // Second attempt if first fails
        if (empty($subtitleFiles)) {
            $this->runYtDlpCommand($videoId, [
                '--skip-download',
                '--write-auto-sub',
                '--sub-format', 'vtt',
                '--sub-lang', 'en',
                '--output', 'subtitle'
            ]);
            
            $subtitleFiles = glob($tempDir . "/subtitle*.vtt") ?:
                           glob($tempDir . "/subtitle*.srt") ?:
                           glob($tempDir . "/subtitle*");
        }
        
        if (empty($subtitleFiles)) {
            return null;
        }
        
        return file_get_contents($subtitleFiles[0]);
    }
    
    /**
     * Run yt-dlp command with given options
     */
    private function runYtDlpCommand(string $videoId, array $options): void
    {
        $command = array_merge(['yt-dlp'], $options, ["https://www.youtube.com/watch?v=$videoId"]);
        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();
    }
    
    /**
     * Clean and format subtitle content
     */
    private function cleanSubtitleContent(string $content): string
    {
        $lines = explode("\n", $content);
        $cleanedLines = [];
        $processingStarted = false;
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);
            
            if (empty($trimmedLine)) {
                continue;
            }
            
            // Skip WebVTT header and metadata
            if (!$processingStarted) {
                if (preg_match('/^WEBVTT|^Language:|^NOTE|^Kind:|^Style:/', $trimmedLine)) {
                    continue;
                }
                $processingStarted = true;
            }
            
            // Skip timestamp and metadata lines
            if ($this->isTimestampOrMetadata($trimmedLine)) {
                continue;
            }
            
            // Clean HTML-like tags and timestamps
            $cleanedLine = $this->cleanLine($trimmedLine);
            
            if (empty(trim($cleanedLine))) {
                continue;
            }
            
            $cleanedLines[] = trim($cleanedLine);
        }
        
        // Join lines and perform final cleanup
        $text = implode(' ', $cleanedLines);
        return $this->finalTextCleanup($text);
    }
    
    /**
     * Check if a line is timestamp or metadata
     */
    private function isTimestampOrMetadata(string $line): bool
    {
        return preg_match('/^\d{2}:\d{2}:\d{2}/', $line) || 
               preg_match('/^\d+$/', $line) || 
               preg_match('/-->/', $line) ||
               preg_match('/^[\[\(].+[\]\)]$/', $line);
    }
    
    /**
     * Clean HTML-like tags from line
     */
    private function cleanLine(string $line): string
    {
        $cleaned = preg_replace('/<\/?[a-zA-Z][^>]*>/', '', $line);
        return preg_replace('/<\d{2}:\d{2}:\d{2}\.\d{3}>/', '', $cleaned);
    }
    
    /**
     * Perform final text cleanup (spacing, capitalization)
     */
    private function finalTextCleanup(string $text): string
    {
        // Fix spacing around punctuation
        $text = preg_replace('/\s+([.,!?:;])/', '$1', $text);
        
        // Fix multiple spaces
        $text = preg_replace('/\s{2,}/', ' ', $text);
        
        // Capitalize first letter after period
        $text = preg_replace_callback('/\.\s+([a-z])/', function($matches) {
            return '. ' . strtoupper($matches[1]);
        }, $text);
        
        // Ensure first character is uppercase
        return strlen($text) > 0 ? ucfirst($text) : $text;
    }
} 