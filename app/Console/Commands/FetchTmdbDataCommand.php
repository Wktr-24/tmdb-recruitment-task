<?php

namespace App\Console\Commands;

use App\Jobs\FetchGenresJob;
use App\Jobs\FetchMoviesJob;
use App\Jobs\FetchSeriesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class FetchTmdbDataCommand extends Command
{
    protected $signature = 'tmdb:fetch';

    protected $description = 'Fetch movies, series and genres from TMDB API';

    public function handle(): int
    {
        if (empty(config('services.tmdb.token'))) {
            $this->error('TMDB_API_TOKEN is not set. Please set it in your .env file.');

            return self::FAILURE;
        }

        $this->info('Dispatching TMDB fetch jobs...');

        Bus::chain([
            new FetchGenresJob,
            new FetchMoviesJob,
            new FetchSeriesJob,
        ])->dispatch();

        $this->info('Jobs dispatched successfully. Run queue:work to process them.');

        return self::SUCCESS;
    }
}
