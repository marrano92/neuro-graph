<?php
// [ai-generated-code]
namespace App\Providers;

use App\Discovery\ControllerStructureScout;
use App\Discovery\ModelStructureScout;
use App\Discovery\TransformerStructureScout;
use Illuminate\Support\ServiceProvider;
use Spatie\StructureDiscoverer\Support\StructureScoutManager;

class StructureDiscovererServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register our structure scouts
        StructureScoutManager::add(ModelStructureScout::class);
        StructureScoutManager::add(ControllerStructureScout::class);
        StructureScoutManager::add(TransformerStructureScout::class);

        // Cache structure scouts in production
        if ($this->app->environment('production')) {
            $this->app->booted(function () {
                StructureScoutManager::cache();
            });
        }
    }
} 