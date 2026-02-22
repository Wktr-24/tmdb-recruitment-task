<?php

namespace App\Http\Resources;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Movie */
class MovieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'title' => (string) $this->title,
            'overview' => (string) $this->overview,
            'release_date' => $this->release_date?->toDateString(),
            'poster_path' => $this->poster_path,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'popularity' => $this->popularity,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
        ];
    }
}
