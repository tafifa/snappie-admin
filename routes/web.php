<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add login route for welcome page compatibility
Route::get('/login', function () {
    return redirect('/api/docs');
})->name('login');
