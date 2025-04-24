<?php
// [ai-generated-code]
namespace App\Discovery;

use App\Contracts\GraphTransformer;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\StructureScout;

class TransformerStructureScout extends StructureScout
{
    /**
     * Define what structures this scout should discover.
     */
    protected function definition(): Discover
    {
        return Discover::in(app_path())
            ->classes()
            ->implementing(GraphTransformer::class);
    }
} 