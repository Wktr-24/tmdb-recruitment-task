<?php

namespace App\Services;

use App\Contracts\TmdbServiceInterface;
use Illuminate\Support\Facades\Http;

class TmdbService implements TmdbServiceInterface
{
    private string $baseUrl = 'https://api.themoviedb.org/3';

    private string $token;

    public function __construct()
    {
        $this->token = config('services.tmdb.token');
    }

    public function fetchMovies(int $page, string $language): array
    {
        return $this->request('/movie/popular', [
            'page' => $page,
            'language' => $language,
        ]);
    }

    public function fetchSeries(int $page, string $language): array
    {
        return $this->request('/tv/popular', [
            'page' => $page,
            'language' => $language,
        ]);
    }

    public function fetchGenres(string $type, string $language): array
    {
        return $this->request("/genre/{$type}/list", [
            'language' => $language,
        ]);
    }

    private function request(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->token)
            ->retry(3, 500)
            ->get($this->baseUrl.$endpoint, $params);

        $response->throw();

        return $response->json();
    }
}
