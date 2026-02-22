<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($movies as $movie)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm">
                @if($movie->poster_path)
                    <img src="https://image.tmdb.org/t/p/w500{{ $movie->poster_path }}"
                         alt="{{ $movie->title }}"
                         class="w-full h-[375px] object-cover">
                @else
                    <div class="w-full h-[375px] bg-gray-100 flex items-center justify-center text-gray-400">
                        {{ __('No poster') }}
                    </div>
                @endif

                <div class="p-4">
                    <h3 class="font-semibold text-lg mb-2">
                        {{ $movie->title }}
                    </h3>

                    <div class="flex justify-between items-center mb-2 text-sm text-gray-500">
                        <span>{{ $movie->release_date?->format('Y-m-d') ?? 'N/A' }}</span>
                        <span class="font-semibold text-amber-500">{{ $movie->vote_average }}/10</span>
                    </div>

                    <div class="flex flex-wrap gap-1">
                        @foreach($movie->genres as $genre)
                            <span class="bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full text-xs">
                                {{ $genre->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-500 py-8">
                {{ __('No movies found.') }} {{ __('Run') }} <code>php artisan tmdb:fetch</code> {{ __('to import data.') }}
            </p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $movies->links() }}
    </div>
</div>
