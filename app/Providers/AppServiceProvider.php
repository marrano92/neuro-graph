<?php

namespace App\Providers;

use App\Services\ArticleProcessorService;
use App\Services\ContentProcessingProgressTracker;
use App\Services\ContentProcessorService;
use App\Services\YoutubeTranscriptionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register content processor services
        $this->app->singleton(ContentProcessorService::class);
        $this->app->singleton(YoutubeTranscriptionService::class);
        $this->app->singleton(ArticleProcessorService::class);
        $this->app->singleton(ContentProcessingProgressTracker::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
