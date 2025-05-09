<?php
// [ai-generated-code]

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Transcript extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'processed' => 'boolean',
        'duration_seconds' => 'integer',
        'token_count' => 'integer',
    ];

    /**
     * Get the content associated with this transcript.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    /**
     * Get the chunks for this transcript.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(TranscriptChunk::class);
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
            'content_id' => $this->content_id,
            'full_text' => $this->full_text,
            'language' => $this->language,
            'source_url' => $this->source_url,
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return config('scout.enabled', true) && $this->processed;
    }
    
    /**
     * Get chunk by time position
     *
     * @param float $timeInSeconds
     * @return TranscriptChunk|null
     */
    public function getChunkByTime(float $timeInSeconds): ?TranscriptChunk
    {
        return $this->chunks()
            ->where('start_time', '<=', $timeInSeconds)
            ->where('end_time', '>=', $timeInSeconds)
            ->first();
    }
    
    /**
     * Get chunks within a time range
     *
     * @param float $startTime
     * @param float $endTime
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChunksInTimeRange(float $startTime, float $endTime)
    {
        return $this->chunks()
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->orderBy('start_time')
            ->get();
    }
} 