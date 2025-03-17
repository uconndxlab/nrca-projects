<?php

use App\Http\Controllers\ProjectController;

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::resource('projects', ProjectController::class);

Route::resource('categories', CategoryController::class);

// Filter projects by category or year
Route::get('/projects/filter', [ProjectController::class, 'filter'])->name('projects.filter');

Route::resource('categories', CategoryController::class);

Route::middleware('cas.auth')->group(function () {
    Route::get('/up', function () {
        return response()->json(['status' => 'up']);
    });
});

Route::get('/logout', function () {
    cas()->logout();
})->name('logout');