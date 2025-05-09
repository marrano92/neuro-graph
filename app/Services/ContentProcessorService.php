<?php
// [ai-generated-code]

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContentProcessorService
{
    /**
     * Process content from URL
     */
    public function processFromUrl(string $url): Content
    {
        $sourceType = $this->determineSourceType($url);
        
        $content = new Content();
        $content->source_url = $url;
        $content->source_type = $sourceType;
        $content->title = "Processing $sourceType content...";
        $content->save();
        
        return $content;
    }
    
    /**
     * Determine the type of content from URL
     */
    public function determineSourceType(string $url): string
    {
        if (Str::contains($url, ['youtube.com', 'youtu.be'])) {
            return 'youtube';
        }
        
        return 'article';
    }
    
    /**
     * Extract YouTube video ID from URL
     */
    public function extractYoutubeId(string $url): ?string
    {
        $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
} 