<?php

namespace Tests\Feature\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreApiTest extends TestCase
{
    use RefreshDatabase;

    private function createGenre(array $overrides = []): Genre
    {
        return Genre::create(array_merge([
            'tmdb_id' => fake()->unique()->numberBetween(1, 99999),
            'name' => ['en' => 'Action', 'pl' => 'Akcja', 'de' => 'Action'],
        ], $overrides));
    }

    public function test_can_list_genres(): void
    {
        $this->createGenre();
        $this->createGenre(['tmdb_id' => 99998, 'name' => ['en' => 'Drama', 'pl' => 'Dramat', 'de' => 'Drama']]);

        $response = $this->getJson('/api/genres');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'tmdb_id', 'name'],
                ],
                'links',
                'meta',
            ]);
    }

    public function test_can_show_genre(): void
    {
        $genre = $this->createGenre();

        $response = $this->getJson("/api/genres/{$genre->id}");

        $response->assertOk()
            ->assertJsonPath('data.tmdb_id', $genre->tmdb_id)
            ->assertJsonPath('data.name', 'Action');
    }

    public function test_genres_are_paginated(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->createGenre(['tmdb_id' => $i + 1]);
        }

        $response = $this->getJson('/api/genres?per_page=5&page=2');

        $response->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    public function test_accept_language_pl(): void
    {
        $this->createGenre();

        $response = $this->getJson('/api/genres', ['Accept-Language' => 'pl']);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Akcja');
    }

    public function test_accept_language_de(): void
    {
        $this->createGenre();

        $response = $this->getJson('/api/genres', ['Accept-Language' => 'de']);

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Action');
    }

    public function test_show_returns_404_for_nonexistent_genre(): void
    {
        $response = $this->getJson('/api/genres/999');

        $response->assertNotFound();
    }
}
