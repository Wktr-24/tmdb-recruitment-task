<?php

namespace Tests\Feature\Api;

use App\Models\Genre;
use App\Models\Serie;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SerieApiTest extends TestCase
{
    use RefreshDatabase;

    private function createSerie(array $overrides = []): Serie
    {
        return Serie::create(array_merge([
            'tmdb_id' => fake()->unique()->numberBetween(1, 99999),
            'name' => ['en' => 'Test Series', 'pl' => 'Testowy Serial', 'de' => 'Testserie'],
            'overview' => ['en' => 'A test series.', 'pl' => 'Testowy serial.', 'de' => 'Eine Testserie.'],
            'first_air_date' => '2024-03-01',
            'poster_path' => '/test.jpg',
            'vote_average' => 8.0,
            'vote_count' => 200,
            'popularity' => 75.456,
        ], $overrides));
    }

    public function test_can_list_series(): void
    {
        $this->createSerie();
        $this->createSerie(['tmdb_id' => 99998]);

        $response = $this->getJson('/api/series');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'tmdb_id', 'name', 'overview', 'first_air_date', 'poster_path', 'vote_average', 'vote_count', 'popularity', 'genres'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_show_serie(): void
    {
        $serie = $this->createSerie();

        $response = $this->getJson("/api/series/{$serie->id}");

        $response->assertOk()
            ->assertJsonPath('data.tmdb_id', $serie->tmdb_id)
            ->assertJsonPath('data.name', 'Test Series');
    }

    public function test_series_are_paginated(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createSerie(['tmdb_id' => $i + 1]);
        }

        $response = $this->getJson('/api/series?per_page=5&page=2');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_accept_language_pl(): void
    {
        $this->createSerie();

        $response = $this->getJson('/api/series', ['Accept-Language' => 'pl']);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Testowy Serial');
    }

    public function test_accept_language_de(): void
    {
        $this->createSerie();

        $response = $this->getJson('/api/series', ['Accept-Language' => 'de']);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Testserie');
    }

    public function test_serie_includes_genres(): void
    {
        $serie = $this->createSerie();
        $genre = Genre::create([
            'tmdb_id' => 18,
            'name' => ['en' => 'Drama', 'pl' => 'Dramat', 'de' => 'Drama'],
        ]);
        $serie->genres()->attach($genre);

        $response = $this->getJson("/api/series/{$serie->id}");

        $response->assertOk()
            ->assertJsonPath('data.genres.0.name', 'Drama');
    }

    public function test_show_returns_404_for_nonexistent_serie(): void
    {
        $response = $this->getJson('/api/series/999');

        $response->assertNotFound();
    }
}
