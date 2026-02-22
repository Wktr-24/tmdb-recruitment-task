<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/** @property \Illuminate\Support\Carbon|null $release_date */
class Movie extends Model
{
    use HasTranslations;

    protected $fillable = [
        'tmdb_id',
        'title',
        'overview',
        'release_date',
        'poster_path',
        'vote_average',
        'vote_count',
        'popularity',
    ];

    public array $translatable = ['title', 'overview'];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'vote_average' => 'decimal:1',
            'popularity' => 'decimal:3',
        ];
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
