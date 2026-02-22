<?php

namespace App\Contracts;

interface TmdbServiceInterface
{
    public function fetchMovies(int $page, string $language): array;

    public function fetchSeries(int $page, string $language): array;

    public function fetchGenres(string $type, string $language): array;
}
