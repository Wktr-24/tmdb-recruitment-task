<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MovieResource;
use App\Models\Movie;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Movies')]
class MovieController extends Controller
{
    #[QueryParameter('per_page', description: 'Number of items per page (1-100)', type: 'integer', default: 15, example: 10)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(100, max(1, $request->integer('per_page', 15)));

        $movies = Movie::with('genres')
            ->orderByDesc('popularity')
            ->paginate($perPage);

        return MovieResource::collection($movies);
    }

    public function show(Movie $movie): MovieResource
    {
        $movie->load('genres');

        return new MovieResource($movie);
    }
}
