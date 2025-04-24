<?php
// [ai-generated-code]
namespace App\Discovery;

use App\Http\Controllers\Controller;
use Spatie\StructureDiscoverer\Discover;
use Spatie\StructureDiscoverer\StructureScout;

class ControllerStructureScout extends StructureScout
{
    /**
     * Define what structures this scout should discover.
     */
    protected function definition(): Discover
    {
        return Discover::in(app_path('Http/Controllers'))
            ->classes()
            ->extending(Controller::class);
    }
} 