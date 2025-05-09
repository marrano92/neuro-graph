<?php
// [ai-generated-code]

namespace App\Services;

use App\Models\Content;
use Illuminate\Support\Facades\Cache;

class ContentProcessingProgressTracker
{
    /**
     * Get cache key for content progress
     */
    protected function getCacheKey(int $contentId): string
    {
        return "content_processing_progress:{$contentId}";
    }
    
    /**
     * Start tracking progress for a content
     */
    public function startTracking(Content $content, int $totalSteps = 100): void
    {
        $key = $this->getCacheKey($content->id);
        
        Cache::put($key, [
            'current_step' => 0,
            'total_steps' => $totalSteps,
            'status' => 'processing',
            'message' => 'Processing has started',
            'started_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(24));
    }
    
    /**
     * Update progress for a content
     */
    public function updateProgress(Content $content, int $currentStep, string $message = null): void
    {
        $key = $this->getCacheKey($content->id);
        
        if (!Cache::has($key)) {
            $this->startTracking($content);
        }
        
        $progress = Cache::get($key);
        $progress['current_step'] = $currentStep;
        
        if ($message !== null) {
            $progress['message'] = $message;
        }
        
        $progress['updated_at'] = now()->toIso8601String();
        
        Cache::put($key, $progress, now()->addHours(24));
    }
    
    /**
     * Complete tracking process
     */
    public function completeTracking(Content $content, string $message = 'Processing completed successfully'): void
    {
        $key = $this->getCacheKey($content->id);
        
        if (!Cache::has($key)) {
            return;
        }
        
        $progress = Cache::get($key);
        $progress['current_step'] = $progress['total_steps'];
        $progress['status'] = 'completed';
        $progress['message'] = $message;
        $progress['completed_at'] = now()->toIso8601String();
        $progress['updated_at'] = now()->toIso8601String();
        
        Cache::put($key, $progress, now()->addHours(24));
    }
    
    /**
     * Fail tracking process
     */
    public function failTracking(Content $content, string $errorMessage = 'Processing failed'): void
    {
        $key = $this->getCacheKey($content->id);
        
        if (!Cache::has($key)) {
            return;
        }
        
        $progress = Cache::get($key);
        $progress['status'] = 'failed';
        $progress['message'] = $errorMessage;
        $progress['failed_at'] = now()->toIso8601String();
        $progress['updated_at'] = now()->toIso8601String();
        
        Cache::put($key, $progress, now()->addHours(24));
    }
    
    /**
     * Get progress data for a content
     * 
     * @return array|null Progress data or null if not found
     */
    public function getProgress(Content $content): ?array
    {
        $key = $this->getCacheKey($content->id);
        
        if (!Cache::has($key)) {
            return null;
        }
        
        return Cache::get($key);
    }
    
    /**
     * Get progress percentage
     */
    public function getProgressPercentage(Content $content): int
    {
        $progress = $this->getProgress($content);
        
        if (!$progress) {
            return 0;
        }
        
        return (int) floor(($progress['current_step'] / $progress['total_steps']) * 100);
    }
    
    /**
     * Check if content processing is complete
     */
    public function isCompleted(Content $content): bool
    {
        $progress = $this->getProgress($content);
        
        if (!$progress) {
            return false;
        }
        
        return $progress['status'] === 'completed';
    }
    
    /**
     * Check if content processing has failed
     */
    public function hasFailed(Content $content): bool
    {
        $progress = $this->getProgress($content);
        
        if (!$progress) {
            return false;
        }
        
        return $progress['status'] === 'failed';
    }
} 