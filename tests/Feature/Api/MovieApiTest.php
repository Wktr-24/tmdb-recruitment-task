<?php

namespace Tests\Feature\Api;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovieApiTest extends TestCase
{
    use RefreshDatabase;

    private function createMovie(array $overrides = []): Movie
    {
        $movie = Movie::create(array_merge([
            'tmdb_id' => fake()->unique()->numberBetween(1, 99999),
            'title' => ['en' => 'Test Movie', 'pl' => 'Testowy Film', 'de' => 'Testfilm'],
            'overview' => ['en' => 'A test movie.', 'pl' => 'Testowy film.', 'de' => 'Ein Testfilm.'],
            'release_date' => '2024-01-15',
            'poster_path' => '/test.jpg',
            'vote_average' => 7.5,
            'vote_count' => 100,
            'popularity' => 50.123,
        ], $overrides));

        return $movie;
    }

    public function test_can_list_movies(): void
    {
        $this->createMovie();
        $this->createMovie(['tmdb_id' => 99998]);

        $response = $this->getJson('/api/movies');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'tmdb_id', 'title', 'overview', 'release_date', 'poster_path', 'vote_average', 'vote_count', 'popularity', 'genres'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_show_movie(): void
    {
        $movie = $this->createMovie();

        $response = $this->getJson("/api/movies/{$movie->id}");

        $response->assertOk()
            ->assertJsonPath('data.tmdb_id', $movie->tmdb_id)
            ->assertJsonPath('data.title', 'Test Movie');
    }

    public function test_movies_are_paginated(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createMovie(['tmdb_id' => $i + 1]);
        }

        $response = $this->getJson('/api/movies?per_page=5&page=2');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_accept_language_pl(): void
    {
        $this->createMovie();

        $response = $this->getJson('/api/movies', ['Accept-Language' => 'pl']);

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Testowy Film');
    }

    public function test_accept_language_de(): void
    {
        $this->createMovie();

        $response = $this->getJson('/api/movies', ['Accept-Language' => 'de']);

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Testfilm');
    }

    public function test_movie_includes_genres(): void
    {
        $movie = $this->createMovie();
        $genre = Genre::create([
            'tmdb_id' => 28,
            'name' => ['en' => 'Action', 'pl' => 'Akcja', 'de' => 'Action'],
        ]);
        $movie->genres()->attach($genre);

        $response = $this->getJson("/api/movies/{$movie->id}");

        $response->assertOk()
            ->assertJsonPath('data.genres.0.name', 'Action');
    }

    public function test_show_returns_404_for_nonexistent_movie(): void
    {
        $response = $this->getJson('/api/movies/999');

        $response->assertNotFound();
    }
}
