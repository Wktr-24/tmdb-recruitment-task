<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SerieResource;
use App\Models\Serie;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

#[Group('Series')]
class SerieController extends Controller
{
    #[QueryParameter('per_page', description: 'Number of items per page (1-100)', type: 'integer', default: 15, example: 10)]
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min(100, max(1, $request->integer('per_page', 15)));

        $series = Serie::with('genres')
            ->orderByDesc('popularity')
            ->paginate($perPage);

        return SerieResource::collection($series);
    }

    public function show(Serie $serie): SerieResource
    {
        $serie->load('genres');

        return new SerieResource($serie);
    }
}
