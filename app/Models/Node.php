<?php
// [ai-generated-code]
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Node extends Model
{
    use HasFactory, Searchable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'label',
        'description',
        'type',
        'source',
        'embedding',
        'color',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'embedding' => 'array',
    ];
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'description' => $this->description,
            'type' => $this->type,
            'source' => $this->source,
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