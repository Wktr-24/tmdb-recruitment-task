<?php

namespace App\Jobs;

use App\Contracts\TmdbServiceInterface;
use App\Models\Genre;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchGenresJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    private const LANGUAGES = ['en', 'pl', 'de'];

    private const TYPES = ['movie', 'tv'];

    public function handle(TmdbServiceInterface $tmdb): void
    {
        foreach (self::TYPES as $type) {
            foreach (self::LANGUAGES as $language) {
                $data = $tmdb->fetchGenres($type, $language);

                foreach ($data['genres'] ?? [] as $genreData) {
                    $genre = Genre::firstOrCreate(
                        ['tmdb_id' => $genreData['id']],
                        ['name' => [$language => $genreData['name']]],
                    );

                    $genre->setTranslation('name', $language, $genreData['name']);
                    $genre->save();
                }
            }
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('FetchGenresJob failed: '.$exception->getMessage());
    }
}
