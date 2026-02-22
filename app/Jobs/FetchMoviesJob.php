<?php

namespace App\Jobs;

use App\Contracts\TmdbServiceInterface;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchMoviesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    private const LIMIT = 50;

    private const MAX_PAGES = 10;

    public function handle(TmdbServiceInterface $tmdb): void
    {
        $tmdbIds = [];
        $page = 1;

        // First pass with English to create/update base records
        while (count($tmdbIds) < self::LIMIT && $page <= self::MAX_PAGES) {
            $data = $tmdb->fetchMovies($page, 'en');
            $results = $data['results'] ?? [];

            if (empty($results)) {
                break;
            }

            foreach ($results as $movieData) {
                if (count($tmdbIds) >= self::LIMIT) {
                    break;
                }

                if (in_array($movieData['id'], $tmdbIds)) {
                    continue;
                }

                $tmdbIds[] = $movieData['id'];

                $movie = Movie::updateOrCreate(
                    ['tmdb_id' => $movieData['id']],
                    [
                        'title' => ['en' => $movieData['title']],
                        'overview' => ['en' => $movieData['overview']],
                        'release_date' => $movieData['release_date'] ?: null,
                        'poster_path' => $movieData['poster_path'],
                        'vote_average' => $movieData['vote_average'],
                        'vote_count' => $movieData['vote_count'],
                        'popularity' => $movieData['popularity'],
                    ]
                );

                $genreIds = Genre::whereIn('tmdb_id', $movieData['genre_ids'] ?? [])
                    ->pluck('id')
                    ->toArray();
                $movie->genres()->sync($genreIds);
            }

            $page++;
        }

        // Fetch translations for other languages
        $maxPage = $page;
        foreach (['pl', 'de'] as $language) {
            for ($p = 1; $p < $maxPage; $p++) {
                $data = $tmdb->fetchMovies($p, $language);

                foreach ($data['results'] ?? [] as $movieData) {
                    if (! in_array($movieData['id'], $tmdbIds)) {
                        continue;
                    }

                    if ($movieData['title'] === $movieData['original_title']) {
                        continue;
                    }

                    $movie = Movie::where('tmdb_id', $movieData['id'])->first();
                    if ($movie) {
                        $movie->setTranslation('title', $language, $movieData['title']);
                        $movie->setTranslation('overview', $language, $movieData['overview']);
                        $movie->save();
                    }
                }
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('FetchMoviesJob failed: '.$exception->getMessage());
    }
}
