<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/movies');

Route::get('/movies', function () {
    return view('movies');
});
