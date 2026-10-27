<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('projects.index');
});

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');

// Filter projects by category or year
Route::get('/projects/filter', [ProjectController::class, 'filter'])->name('projects.filter');

// Entra ID Authentication routes
Route::get('/entra/redirect', [AuthController::class, 'redirect'])->name('entra.redirect');
Route::get('/signin-oidc', [AuthController::class, 'callback'])->name('entra.callback');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Staff & Admin Routes
Route::middleware('entra')->group(function () {
    // Project Management
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::get('/projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::put('/projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Admin Hub
    Route::get('/admin', [AdminController::class, 'index'])->name('admin');

    // Admin User Management
    Route::post('/admin/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::post('/admin/users/{user}/toggle', [AdminController::class, 'toggleUserAdmin'])->name('admin.users.toggle');
    Route::delete('/admin/users/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Admin Program Management
    Route::post('/admin/programs', [AdminController::class, 'storeProgram'])->name('admin.programs.store');
    Route::put('/admin/programs/{program}', [AdminController::class, 'updateProgram'])->name('admin.programs.update');
    Route::delete('/admin/programs/{program}', [AdminController::class, 'destroyProgram'])->name('admin.programs.destroy');

    // Admin Year Management
    Route::post('/admin/years', [AdminController::class, 'storeYear'])->name('admin.years.store');
    Route::put('/admin/years/{year}', [AdminController::class, 'updateYear'])->name('admin.years.update');
    Route::delete('/admin/years/{year}', [AdminController::class, 'destroyYear'])->name('admin.years.destroy');

    // Admin County Management
    Route::post('/admin/counties', [AdminController::class, 'storeCounty'])->name('admin.counties.store');
    Route::put('/admin/counties/{county}', [AdminController::class, 'updateCounty'])->name('admin.counties.update');
    Route::delete('/admin/counties/{county}', [AdminController::class, 'destroyCounty'])->name('admin.counties.destroy');

    // Counties Normalization
    Route::post('/admin/normalize-counties', [AdminController::class, 'normalizeCounties'])->name('admin.normalize-counties');

    // Category / Tag Management
    Route::resource('categories', CategoryController::class);

    Route::get('/up', function () {
        return response()->json(['status' => 'up']);
    });
});