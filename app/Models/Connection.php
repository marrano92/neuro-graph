<?php
// [ai-generated-code]
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'source_id',
        'target_id',
        'type',
        'weight',
    ];

    /**
     * Get the source node associated with the connection.
     */
    public function sourceNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'source_id');
    }

    /**
     * Get the target node associated with the connection.
     */
    public function targetNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'target_id');
    }
} 