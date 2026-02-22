<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/** @property \Illuminate\Support\Carbon|null $first_air_date */
class Serie extends Model
{
    use HasTranslations;

    protected $table = 'series';

    protected $fillable = [
        'tmdb_id',
        'name',
        'overview',
        'first_air_date',
        'poster_path',
        'vote_average',
        'vote_count',
        'popularity',
    ];

    public array $translatable = ['name', 'overview'];

    protected function casts(): array
    {
        return [
            'first_air_date' => 'date',
            'vote_average' => 'decimal:1',
            'popularity' => 'decimal:3',
        ];
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }
}
