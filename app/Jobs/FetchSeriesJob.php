<?php

namespace App\Jobs;

use App\Contracts\TmdbServiceInterface;
use App\Models\Genre;
use App\Models\Serie;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchSeriesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    private const LIMIT = 10;

    public function handle(TmdbServiceInterface $tmdb): void
    {
        $tmdbIds = [];

        // First pass with English to create/update base records
        $data = $tmdb->fetchSeries(1, 'en');

        foreach ($data['results'] ?? [] as $serieData) {
            if (count($tmdbIds) >= self::LIMIT) {
                break;
            }

            if (in_array($serieData['id'], $tmdbIds)) {
                continue;
            }

            $tmdbIds[] = $serieData['id'];

            $serie = Serie::updateOrCreate(
                ['tmdb_id' => $serieData['id']],
                [
                    'name' => ['en' => $serieData['name']],
                    'overview' => ['en' => $serieData['overview']],
                    'first_air_date' => $serieData['first_air_date'] ?: null,
                    'poster_path' => $serieData['poster_path'],
                    'vote_average' => $serieData['vote_average'],
                    'vote_count' => $serieData['vote_count'],
                    'popularity' => $serieData['popularity'],
                ]
            );

            $genreIds = Genre::whereIn('tmdb_id', $serieData['genre_ids'] ?? [])
                ->pluck('id')
                ->toArray();
            $serie->genres()->sync($genreIds);
        }

        // Fetch translations for other languages
        foreach (['pl', 'de'] as $language) {
            $data = $tmdb->fetchSeries(1, $language);

            foreach ($data['results'] ?? [] as $serieData) {
                if (! in_array($serieData['id'], $tmdbIds)) {
                    continue;
                }

                if ($serieData['name'] === $serieData['original_name']) {
                    continue;
                }

                $serie = Serie::where('tmdb_id', $serieData['id'])->first();
                if ($serie) {
                    $serie->setTranslation('name', $language, $serieData['name']);
                    $serie->setTranslation('overview', $language, $serieData['overview']);
                    $serie->save();
                }
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('FetchSeriesJob failed: '.$exception->getMessage());
    }
}
