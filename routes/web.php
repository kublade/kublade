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
    Route::get('/projects/{project_id}/users', [App\Http\Controllers\ProjectController::class, 'page_users'])->name('project.users');
    Route::get('/projects/{project_id}/invitations/create', [App\Http\Controllers\ProjectController::class, 'page_invitation_create'])->name('project.invitation.create');
    Route::post('/projects/{project_id}/invitations/create', [App\Http\Controllers\ProjectController::class, 'action_invitation_create'])->name('project.invitation.create.action');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/delete', [App\Http\Controllers\ProjectController::class, 'action_invitation_delete'])->name('project.invitation.delete.action');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/accept', [App\Http\Controllers\ProjectController::class, 'action_invitation_accept'])->name('project.invitation.accept.action');
    Route::get('/projects/{project_id}', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.details');

    Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.index');
    Route::get('/templates/add', [App\Http\Controllers\TemplateController::class, 'page_add'])->name('template.add');
    Route::post('/templates/add', [App\Http\Controllers\TemplateController::class, 'action_add'])->name('template.add.action');
    Route::get('/templates/import', [App\Http\Controllers\TemplateController::class, 'page_import'])->name('template.import');
    Route::post('/templates/import', [App\Http\Controllers\TemplateController::class, 'action_import'])->name('template.import.action');
    Route::get('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update'])->name('template.update');
    Route::post('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update'])->name('template.update.action');
    Route::get('/templates/{template_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete'])->name('template.delete.action');
    Route::get('/templates/{template_id}', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.details');

    Route::get('/templates/{template_id}/folder/add', [App\Http\Controllers\TemplateController::class, 'page_add_folder'])->name('template.folder.add');
    Route::post('/templates/{template_id}/folder/add', [App\Http\Controllers\TemplateController::class, 'action_add_folder'])->name('template.folder.add.action');
    Route::get('/templates/{template_id}/folder/{folder_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_folder'])->name('template.folder.update');
    Route::post('/templates/{template_id}/folder/{folder_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_folder'])->name('template.folder.update.action');
    Route::get('/templates/{template_id}/folder/{folder_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_folder'])->name('template.folder.delete.action');
    Route::get('/templates/{template_id}/file/add', [App\Http\Controllers\TemplateController::class, 'page_add_file'])->name('template.file.add');
    Route::post('/templates/{template_id}/file/add', [App\Http\Controllers\TemplateController::class, 'action_add_file'])->name('template.file.add.action');
    Route::get('/templates/{template_id}/file/{file_id}', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.details_file');
    Route::get('/templates/{template_id}/file/{file_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_file'])->name('template.file.update');
    Route::post('/templates/{template_id}/file/{file_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_file'])->name('template.file.update.action');
    Route::get('/templates/{template_id}/file/{file_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_file'])->name('template.file.delete.action');

    Route::get('/templates/{template_id}/field/add', [App\Http\Controllers\TemplateController::class, 'page_add_field'])->name('template.field.add');
    Route::post('/templates/{template_id}/field/add', [App\Http\Controllers\TemplateController::class, 'action_add_field'])->name('template.field.add.action');
    Route::get('/templates/{template_id}/field/{field_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_field'])->name('template.field.update');
    Route::post('/templates/{template_id}/field/{field_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_field'])->name('template.field.update.action');
    Route::get('/templates/{template_id}/field/{field_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_field'])->name('template.field.delete.action');
    Route::get('/templates/{template_id}/field/{field_id}/option/add', [App\Http\Controllers\TemplateController::class, 'page_add_option'])->name('template.field.option.add');
    Route::post('/templates/{template_id}/field/{field_id}/option/add', [App\Http\Controllers\TemplateController::class, 'action_add_option'])->name('template.field.option.add.action');
    Route::get('/templates/{template_id}/field/{field_id}/option/{option_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_option'])->name('template.field.option.update');
    Route::post('/templates/{template_id}/field/{field_id}/option/{option_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_option'])->name('template.field.option.update.action');
    Route::get('/templates/{template_id}/field/{field_id}/option/{option_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_option'])->name('template.field.option.delete.action');

    Route::get('/templates/{template_id}/port/add', [App\Http\Controllers\TemplateController::class, 'page_add_port'])->name('template.port.add');
    Route::post('/templates/{template_id}/port/add', [App\Http\Controllers\TemplateController::class, 'action_add_port'])->name('template.port.add.action');
    Route::get('/templates/{template_id}/port/{port_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_port'])->name('template.port.update');
    Route::post('/templates/{template_id}/port/{port_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_port'])->name('template.port.update.action');
    Route::get('/templates/{template_id}/port/{port_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_port'])->name('template.port.delete.action');

    Route::get('/projects/{project_id}/clusters', [App\Http\Controllers\ClusterController::class, 'page_index'])->name('cluster.index');
    Route::get('/projects/{project_id}/clusters/add', [App\Http\Controllers\ClusterController::class, 'page_add'])->name('cluster.add');
    Route::post('/projects/{project_id}/clusters/add', [App\Http\Controllers\ClusterController::class, 'action_add'])->name('cluster.add.action');
    Route::get('/projects/{project_id}/clusters/{cluster_id}/update', [App\Http\Controllers\ClusterController::class, 'page_update'])->name('cluster.update');
    Route::post('/projects/{project_id}/clusters/{cluster_id}/update', [App\Http\Controllers\ClusterController::class, 'action_update'])->name('cluster.update.action');
    Route::get('/projects/{project_id}/clusters/{cluster_id}/delete', [App\Http\Controllers\ClusterController::class, 'action_delete'])->name('cluster.delete.action');

    Route::get('/projects/{project_id}/deployments', [App\Http\Controllers\DeploymentController::class, 'page_index'])->name('deployment.index');
    Route::get('/projects/{project_id}/deployments/add', [App\Http\Controllers\DeploymentController::class, 'page_add'])->name('deployment.add');
    Route::post('/projects/{project_id}/deployments/add', [App\Http\Controllers\DeploymentController::class, 'action_add'])->name('deployment.add.action');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/update', [App\Http\Controllers\DeploymentController::class, 'page_update'])->name('deployment.update');
    Route::post('/projects/{project_id}/deployments/{deployment_id}/update', [App\Http\Controllers\DeploymentController::class, 'action_update'])->name('deployment.update.action');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/delete', [App\Http\Controllers\DeploymentController::class, 'action_delete'])->name('deployment.delete.action');
    Route::post('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}/put', [App\Http\Controllers\DeploymentController::class, 'action_put_network_policy'])->name('deployment.netpol.put.action');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}/delete', [App\Http\Controllers\DeploymentController::class, 'action_delete_network_policy'])->name('deployment.netpol.delete.action');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/commit/{commit_id}/revert', [App\Http\Controllers\DeploymentController::class, 'action_revert_commit'])->name('deployment.commit.revert.action');
    Route::get('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\DeploymentController::class, 'page_index'])->name('deployment.details');
});

Route::get('/switch-color-mode', function () {
    return redirect()->back()->withCookie(cookie()->make('theme', request()->cookie('theme') === 'dark' ? 'light' : 'dark', 525960));
})->name('switch-color-mode');
