<?php

namespace Tests\Feature\Jobs;

use App\Contracts\TmdbServiceInterface;
use App\Jobs\FetchMoviesJob;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FetchMoviesJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_fetches_and_stores_movies(): void
    {
        Genre::create([
            'tmdb_id' => 28,
            'name' => ['en' => 'Action'],
        ]);

        $enResponse = [
            'results' => [
                [
                    'id' => 550,
                    'title' => 'Fight Club',
                    'original_title' => 'Fight Club',
                    'overview' => 'A ticking-bomb insomniac...',
                    'release_date' => '1999-10-15',
                    'poster_path' => '/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg',
                    'vote_average' => 8.4,
                    'vote_count' => 26000,
                    'popularity' => 61.416,
                    'genre_ids' => [28],
                ],
            ],
        ];

        $plResponse = [
            'results' => [
                [
                    'id' => 550,
                    'title' => 'Podziemny krąg',
                    'original_title' => 'Fight Club',
                    'overview' => 'Cierpiący na bezsenność...',
                    'release_date' => '1999-10-15',
                    'poster_path' => '/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg',
                    'vote_average' => 8.4,
                    'vote_count' => 26000,
                    'popularity' => 61.416,
                    'genre_ids' => [28],
                ],
            ],
        ];

        $deResponse = [
            'results' => [
                [
                    'id' => 550,
                    'title' => 'Fight Club',
                    'original_title' => 'Fight Club',
                    'overview' => 'Ein Yuppie findet...',
                    'release_date' => '1999-10-15',
                    'poster_path' => '/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg',
                    'vote_average' => 8.4,
                    'vote_count' => 26000,
                    'popularity' => 61.416,
                    'genre_ids' => [28],
                ],
            ],
        ];

        $mock = $this->mock(TmdbServiceInterface::class);
        $mock->shouldReceive('fetchMovies')
            ->with(1, 'en')
            ->once()
            ->andReturn($enResponse);
        $mock->shouldReceive('fetchMovies')
            ->with(2, 'en')
            ->once()
            ->andReturn(['results' => []]);
        $mock->shouldReceive('fetchMovies')
            ->with(1, 'pl')
            ->once()
            ->andReturn($plResponse);
        $mock->shouldReceive('fetchMovies')
            ->with(1, 'de')
            ->once()
            ->andReturn($deResponse);

        (new FetchMoviesJob)->handle($mock);

        $this->assertDatabaseHas('movies', [
            'tmdb_id' => 550,
        ]);

        $movie = Movie::where('tmdb_id', 550)->first();
        $this->assertEquals('Fight Club', $movie->getTranslation('title', 'en'));
        // PL has a real translation (title !== original_title)
        $this->assertEquals('Podziemny krąg', $movie->getTranslation('title', 'pl'));
        // DE title === original_title → skipped, fallback to EN
        $this->assertEquals('Fight Club', $movie->getTranslation('title', 'de'));
        $this->assertCount(1, $movie->genres);
    }

    public function test_handles_empty_response(): void
    {
        $mock = $this->mock(TmdbServiceInterface::class);
        $mock->shouldReceive('fetchMovies')
            ->with(1, 'en')
            ->once()
            ->andReturn(['results' => []]);

        (new FetchMoviesJob)->handle($mock);

        $this->assertDatabaseCount('movies', 0);
    }
}
