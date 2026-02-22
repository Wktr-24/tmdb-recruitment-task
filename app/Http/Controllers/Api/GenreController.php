<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GenreResource;
use App\Models\Genre;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Genres')]
class GenreController extends Controller
{
    #[QueryParameter('per_page', description: 'Number of items per page (1-100)', type: 'integer', default: 15, example: 10)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(100, max(1, $request->integer('per_page', 15)));

        $genres = Genre::paginate($perPage);

        return GenreResource::collection($genres);
    }

    public function show(Genre $genre): GenreResource
    {
        return new GenreResource($genre);
    }
}
