<?php
// [ai-generated-code]

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
    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(Content $content): bool
    {
        // Check if the URL is a YouTube URL
        $youtubePattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+$/i';
        return preg_match($youtubePattern, $content->source_url) === 1;
    }
    
    /**
     * Process the content source and generate a transcript
     */
    public function transcribe(Content $content, string $sourceId): ?Transcript
    {
        try {
            // Check if subtitles are available
            if (!$this->checkSubtitlesAvailable($sourceId)) {
                Log::info("No subtitles available for YouTube video: $sourceId");
                return null;
            }
            
            $subtitles = $this->fetchSubtitles($sourceId);
            
            if (empty($subtitles)) {
                Log::info("Failed to fetch subtitles for YouTube video: $sourceId");
                return null;
            }
            
            // Get video details
            $fetchDetailsCommand = new FetchVideoDetailsCommand();
            $videoDetails = $fetchDetailsCommand->execute($sourceId, 'youtube');
            
            // Update content title if available
            if (isset($videoDetails['title']) && $videoDetails['title'] !== "YouTube Video $sourceId") {
                $content->title = $videoDetails['title'];
                $content->save();
            }
            
            // Create transcript
            return $this->createTranscriptFromText($content, $subtitles, [
                'source_type' => 'youtube_subtitles'
            ]);
        } catch (Exception $e) {
            Log::warning("Failed to get YouTube subtitles: " . $e->getMessage(), [
                'video_id' => $sourceId
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
            // Check if yt-dlp is installed
            $checkProcess = new Process(['which', 'yt-dlp']);
            $checkProcess->run();
            
            if (!$checkProcess->isSuccessful()) {
                Log::error('yt-dlp is not installed in the container. Please add it to your Dockerfile.');
                return false;
            }
            
            // List available subtitles
            $process = new Process([
                'yt-dlp', 
                '--list-subs',
                '--skip-download',
                "https://www.youtube.com/watch?v=$videoId"
            ]);
            
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            $output = $process->getOutput();
            Log::debug("Subtitle check output: " . $output);
            
            // Check if the output contains subtitle information
            return strpos($output, 'Available subtitles') !== false && 
                   (strpos($output, 'has no subtitles') === false || 
                    strpos($output, 'Available automatic captions') !== false);
        } catch (Exception $e) {
            Log::warning("Error checking YouTube subtitles availability: " . $e->getMessage(), [
                'video_id' => $videoId
            ]);
            return false;
        }
    }
    
    /**
     * Fetch subtitles from YouTube
     */
    private function fetchSubtitles(string $videoId): ?string
    {
        try {
            // Create a temporary directory for subtitles
            $tempDir = storage_path('app/temp/youtube_subtitles/' . uniqid());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            
            // Change to the temporary directory
            $currentDir = getcwd();
            chdir($tempDir);
            
            // Try to get automatic captions
            $process = new Process([
                'yt-dlp',
                '--skip-download',
                '--write-auto-sub',
                '--sub-lang', 'en',
                '--output', 'subtitle',
                "https://www.youtube.com/watch?v=$videoId"
            ]);
            
            $process->setTimeout(300); // 5 minutes timeout
            $process->run();
            
            // Log the output for debugging
            Log::debug("yt-dlp output: " . $process->getOutput());
            if (!$process->isSuccessful()) {
                Log::error("yt-dlp stderr: " . $process->getErrorOutput());
                throw new ProcessFailedException($process);
            }
            
            // Find the generated subtitle file
            $subtitleFiles = glob($tempDir . "/subtitle*.en.vtt");
            
            if (empty($subtitleFiles)) {
                // List all files in the directory for debugging
                $allFiles = scandir($tempDir);
                Log::debug("All files in temp directory: " . implode(', ', $allFiles));
                
                // Try another approach with different options
                $process = new Process([
                    'yt-dlp',
                    '--skip-download',
                    '--write-auto-sub',
                    '--sub-format', 'vtt',
                    '--sub-lang', 'en',
                    '--output', 'subtitle',
                    "https://www.youtube.com/watch?v=$videoId"
                ]);
                $process->run();
                
                Log::debug("Second yt-dlp attempt output: " . $process->getOutput());
                
                // Check again for any subtitle files
                $subtitleFiles = glob($tempDir . "/subtitle*.vtt") ?:
                                glob($tempDir . "/subtitle*.srt") ?:
                                glob($tempDir . "/subtitle*");
                
                if (empty($subtitleFiles)) {
                    Log::warning("No subtitle files found after yt-dlp execution for: $videoId");
                    return null;
                }
            }
            
            Log::debug("Found subtitle files: " . implode(", ", $subtitleFiles));
            
            // Read the content of the first subtitle file
            $subtitleContent = file_get_contents($subtitleFiles[0]);
            
            // Clean up the temporary directory
            array_map('unlink', glob($tempDir . "/*"));
            rmdir($tempDir);
            
            // Restore original directory
            chdir($currentDir);
            
            // Clean and format the subtitle content
            return $this->cleanSubtitleContent($subtitleContent);
        } catch (Exception $e) {
            Log::warning("Error fetching YouTube subtitles: " . $e->getMessage(), [
                'video_id' => $videoId,
                'exception' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    
    /**
     * Clean and format subtitle content
     */
    private function cleanSubtitleContent(string $content): string
    {
        // Remove timestamps and metadata
        $lines = explode("\n", $content);
        $cleanedLines = [];
        
        foreach ($lines as $line) {
            // Skip timestamp lines and empty lines
            if (preg_match('/^\d{2}:\d{2}:\d{2}/', $line) || empty(trim($line))) {
                continue;
            }
            
            // Skip lines with just speaker notation or metadata
            if (preg_match('/^[\[\(].+[\]\)]$/', trim($line))) {
                continue;
            }
            
            $cleanedLines[] = trim($line);
        }
        
        // Join the remaining lines with proper spacing
        return implode(' ', $cleanedLines);
    }
} 