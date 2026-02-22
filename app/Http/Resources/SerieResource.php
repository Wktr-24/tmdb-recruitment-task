<?php

namespace App\Http\Resources;

use App\Models\Serie;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Serie */
class SerieResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tmdb_id' => $this->tmdb_id,
            'name' => (string) $this->name,
            'overview' => (string) $this->overview,
            'first_air_date' => $this->first_air_date?->toDateString(),
            'poster_path' => $this->poster_path,
            'vote_average' => $this->vote_average,
            'vote_count' => $this->vote_count,
            'popularity' => $this->popularity,
            'genres' => GenreResource::collection($this->whenLoaded('genres')),
        ];
    }
}
