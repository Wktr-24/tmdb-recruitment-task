<?php

namespace App\Livewire;

use App\Models\Movie;
use Livewire\Component;
use Livewire\WithPagination;

class MovieList extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.movie-list', [
            'movies' => Movie::with('genres')
                ->orderByDesc('popularity')
                ->paginate(12),
        ]);
    }
}
