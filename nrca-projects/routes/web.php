<?php

use App\Http\Controllers\AuthController;
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

// Entra ID Authentication routes
Route::get('/entra/redirect', [AuthController::class, 'redirect'])->name('entra.redirect');
Route::get('/signin-oidc', [AuthController::class, 'callback'])->name('entra.callback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('entra')->group(function () {
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    Route::get('/admin', [ProjectController::class, 'index'])->name('admin');

    Route::get('/up', function () {
        return response()->json(['status' => 'up']);
    });
});