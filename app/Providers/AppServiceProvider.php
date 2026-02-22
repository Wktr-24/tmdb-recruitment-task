<?php

namespace App\Providers;

use App\Contracts\TmdbServiceInterface;
use App\Services\TmdbService;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TmdbServiceInterface::class, TmdbService::class);
    }

    public function boot(): void
    {
        Scramble::configure()->routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/') && ! $route->isFallback;
        });
    }
}
