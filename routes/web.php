<?php

declare(strict_types=1);

use App\Http\Middleware\IdentifyProject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
});

Auth::routes();

Route::middleware([
    IdentifyProject::class,
])->group(function () {
    Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.index');
    Route::get('/projects/add', [App\Http\Controllers\ProjectController::class, 'page_add'])->name('project.add');
    Route::post('/projects/add', [App\Http\Controllers\ProjectController::class, 'action_add'])->name('project.add.action');
    Route::get('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'page_update'])->name('project.update');
    Route::post('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'action_update'])->name('project.update.action');
    Route::get('/projects/{project_id}/delete', [App\Http\Controllers\ProjectController::class, 'action_delete'])->name('project.delete.action');
    Route::get('/projects/{project_id}', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.details');
});
