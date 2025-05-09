<?php
// [ai-generated-code]
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Content extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'source_type',
        'source_url',
        'summary',
    ];

    /**
     * Get the concepts associated with this content.
     */
    public function nodes()
    {
        return $this->belongsToMany(Node::class, 'content_node')
                    ->withTimestamps();
    }
    
    /**
     * Get the transcript associated with this content.
     */
    public function transcript(): HasOne
    {
        return $this->hasOne(Transcript::class);
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
            'title' => $this->title,
            'source_type' => $this->source_type,
            'summary' => $this->summary,
        ];
    }
    
    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return config('scout.enabled', true);
    }
} 