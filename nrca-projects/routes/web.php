<?php

use App\Http\Controllers\ProjectController;

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::resource('projects', ProjectController::class);

Route::resource('categories', CategoryController::class);

// Filter projects by category or year
Route::get('/projects/filter', [ProjectController::class, 'filter'])->name('projects.filter');

Route::resource('categories', CategoryController::class);

Route::middleware('cas.auth')->group(function () {

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

Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    cas()->logout();
    return redirect(cas()->logout()); // Redirect to CAS logout page
})->name('logout');