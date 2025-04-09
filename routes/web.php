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
    Route::get('/projects/invitations', [App\Http\Controllers\ProjectController::class, 'page_invitations'])->name('project.invitations');
    Route::get('/projects/add', [App\Http\Controllers\ProjectController::class, 'page_add'])->name('project.add');
    Route::post('/projects/add', [App\Http\Controllers\ProjectController::class, 'action_add'])->name('project.add.action');
    Route::get('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'page_update'])->name('project.update');
    Route::post('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'action_update'])->name('project.update.action');
    Route::get('/projects/{project_id}/delete', [App\Http\Controllers\ProjectController::class, 'action_delete'])->name('project.delete.action');
    Route::get('/projects/{project_id}', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.details');
    Route::get('/projects/{project_id}/users', [App\Http\Controllers\ProjectController::class, 'page_users'])->name('project.users');
    Route::get('/projects/{project_id}/invitations/create', [App\Http\Controllers\ProjectController::class, 'page_invitation_create'])->name('project.invitation.create');
    Route::post('/projects/{project_id}/invitations/create', [App\Http\Controllers\ProjectController::class, 'action_invitation_create'])->name('project.invitation.create.action');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/delete', [App\Http\Controllers\ProjectController::class, 'action_invitation_delete'])->name('project.invitation.delete.action');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/accept', [App\Http\Controllers\ProjectController::class, 'action_invitation_accept'])->name('project.invitation.accept.action');

    Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.index');
    Route::get('/templates/add', [App\Http\Controllers\TemplateController::class, 'page_add'])->name('template.add');
    Route::post('/templates/add', [App\Http\Controllers\TemplateController::class, 'action_add'])->name('template.add.action');
    Route::get('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update'])->name('template.update');
    Route::post('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update'])->name('template.update.action');
    Route::get('/templates/{template_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete'])->name('template.delete.action');
});
