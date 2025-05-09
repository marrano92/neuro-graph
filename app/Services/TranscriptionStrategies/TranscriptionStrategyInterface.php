<?php
// [ai-generated-code]

namespace App\Services\TranscriptionStrategies;

use App\Models\Content;
use App\Models\Transcript;

interface TranscriptionStrategyInterface
{
    /**
     * Process the content source and generate a transcript
     */
    public function transcribe(Content $content, string $sourceId): ?Transcript;
    
    /**
     * Check if this strategy can handle the given content
     */
    public function canHandle(Content $content): bool;
} 