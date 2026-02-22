<?php

namespace Tests\Feature\Commands;

use App\Jobs\FetchGenresJob;
use App\Jobs\FetchMoviesJob;
use App\Jobs\FetchSeriesJob;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class FetchTmdbDataCommandTest extends TestCase
{
    public function test_command_dispatches_chained_jobs(): void
    {
        Bus::fake();
        config(['services.tmdb.token' => 'test-token']);

        $this->artisan('tmdb:fetch')
            ->expectsOutput('Dispatching TMDB fetch jobs...')
            ->expectsOutput('Jobs dispatched successfully. Run queue:work to process them.')
            ->assertSuccessful();

        Bus::assertChained([
            FetchGenresJob::class,
            FetchMoviesJob::class,
            FetchSeriesJob::class,
        ]);
    }

    public function test_command_fails_without_token(): void
    {
        config(['services.tmdb.token' => '']);

        $this->artisan('tmdb:fetch')
            ->expectsOutput('TMDB_API_TOKEN is not set. Please set it in your .env file.')
            ->assertFailed();
    }
}
