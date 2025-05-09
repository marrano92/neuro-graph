<?php
// [ai-generated-code]

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class TranscriptChunk extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'float',
        'end_time' => 'float',
        'token_count' => 'integer',
        'chunk_index' => 'integer',
    ];

    /**
     * Get the transcript that owns this chunk.
     */
    public function transcript(): BelongsTo
    {
        return $this->belongsTo(Transcript::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'transcript_id' => $this->transcript_id,
            'text' => $this->text,
            'chunk_index' => $this->chunk_index,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return config('scout.enabled', true);
    }
    
    /**
     * Format the time for display
     *
     * @param float $timeInSeconds
     * @return string
     */
    public static function formatTime(float $timeInSeconds): string
    {
        $minutes = floor($timeInSeconds / 60);
        $seconds = $timeInSeconds % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
    
    /**
     * Get formatted start time
     *
     * @return string
     */
    public function getFormattedStartTimeAttribute(): string
    {
        return $this->start_time ? self::formatTime($this->start_time) : '00:00';
    }
    
    /**
     * Get formatted end time
     *
     * @return string
     */
    public function getFormattedEndTimeAttribute(): string
    {
        return $this->end_time ? self::formatTime($this->end_time) : '00:00';
    }
} 