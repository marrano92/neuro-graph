<?php
// [ai-generated-code]
namespace App\Discovery;

use Illuminate\Database\Eloquent\Model;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\DiscoverConditionFactory;
use Spatie\StructureDiscoverer\StructureScout;

class ModelStructureScout extends StructureScout
{
    /**
     * Define what structures this scout should discover.
     */
    protected function definition(): Discover
    {
        return Discover::in(app_path('Models'))
            ->classes()
            ->extending(Model::class);
    }
} 