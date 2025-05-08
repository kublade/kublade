<?php

declare(strict_types=1);

use App\Helpers\API\Response;
use App\Http\Middleware\IdentifyProject;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [App\Http\Controllers\API\AuthController::class, 'register']);
    Route::post('/login', [App\Http\Controllers\API\AuthController::class, 'login']);

    Route::middleware('api.guard')->group(function () {
        Route::get('/me', [App\Http\Controllers\API\AuthController::class, 'me']);
        Route::post('/logout', [App\Http\Controllers\API\AuthController::class, 'logout']);
        Route::post('/refresh', [App\Http\Controllers\API\AuthController::class, 'refresh']);
    });
});

Route::middleware([
    IdentifyProject::class,
])->group(function () {
    Route::get('/projects', [App\Http\Controllers\API\ProjectController::class, 'action_list'])->name('api.project.list');
    Route::post('/projects', [App\Http\Controllers\API\ProjectController::class, 'action_add'])->name('api.project.add');
    Route::get('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_get'])->name('api.project.get');
    Route::patch('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_update'])->name('api.project.update');
    Route::delete('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_delete'])->name('api.project.delete');

    Route::get('/projects/{project_id}/invitations', [App\Http\Controllers\API\ProjectController::class, 'action_list_invitation'])->name('api.project.invitation.list');
    Route::post('/projects/{project_id}/invitations/create', [App\Http\Controllers\API\ProjectController::class, 'action_invitation_create'])->name('api.project.invitation.create.action');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}', [App\Http\Controllers\API\ProjectController::class, 'action_get_invitation'])->name('api.project.invitation.get');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/delete', [App\Http\Controllers\API\ProjectController::class, 'action_invitation_delete'])->name('api.project.invitation.delete');
    Route::get('/projects/{project_id}/invitations/{project_invitation_id}/accept', [App\Http\Controllers\API\ProjectController::class, 'action_invitation_accept'])->name('api.project.invitation.accept');

    Route::get('/templates', [App\Http\Controllers\API\TemplateController::class, 'action_list'])->name('api.template.list');
    Route::post('/templates', [App\Http\Controllers\API\TemplateController::class, 'action_add'])->name('api.template.add');
    Route::post('/templates/import', [App\Http\Controllers\API\TemplateController::class, 'action_import'])->name('api.template.import');
    Route::get('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get'])->name('api.template.get');
    Route::patch('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update'])->name('api.template.update');
    Route::delete('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete'])->name('api.template.delete');

    Route::get('/templates/{template_id}/folders', [App\Http\Controllers\API\TemplateController::class, 'action_list_folder'])->name('api.template.folder.list');
    Route::post('/templates/{template_id}/folders', [App\Http\Controllers\API\TemplateController::class, 'action_add_folder'])->name('api.template.folder.add');
    Route::get('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_folder'])->name('api.template.folder.get');
    Route::patch('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_folder'])->name('api.template.folder.update');
    Route::delete('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_folder'])->name('api.template.folder.delete');

    Route::get('/templates/{template_id}/files', [App\Http\Controllers\API\TemplateController::class, 'action_list_file'])->name('api.template.file.list');
    Route::post('/templates/{template_id}/files', [App\Http\Controllers\API\TemplateController::class, 'action_add_file'])->name('api.template.file.add');
    Route::get('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_file'])->name('api.template.file.get');
    Route::patch('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_file'])->name('api.template.file.update');
    Route::delete('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_file'])->name('api.template.file.delete');

    Route::get('/templates/{template_id}/fields', [App\Http\Controllers\API\TemplateController::class, 'action_list_field'])->name('api.template.field.list');
    Route::post('/templates/{template_id}/fields', [App\Http\Controllers\API\TemplateController::class, 'action_add_field'])->name('api.template.field.add');
    Route::get('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_field'])->name('api.template.field.get');
    Route::patch('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_field'])->name('api.template.field.update');
    Route::delete('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_field'])->name('api.template.field.delete');

    Route::post('/templates/{template_id}/fields/{field_id}/options', [App\Http\Controllers\API\TemplateController::class, 'action_add_option'])->name('api.template.field.option.add');
    Route::patch('/templates/{template_id}/fields/{field_id}/options/{option_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_option'])->name('api.template.field.option.update');
    Route::delete('/templates/{template_id}/fields/{field_id}/options/{option_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_option'])->name('api.template.field.option.delete');

    Route::get('/templates/{template_id}/ports', [App\Http\Controllers\API\TemplateController::class, 'action_list_port'])->name('api.template.port.list');
    Route::post('/templates/{template_id}/ports', [App\Http\Controllers\API\TemplateController::class, 'action_add_port'])->name('api.template.port.add');
    Route::get('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_port'])->name('api.template.port.get');
    Route::patch('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_port'])->name('api.template.port.update');
    Route::delete('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_port'])->name('api.template.port.delete');

    Route::get('/projects/{project_id}/clusters', [App\Http\Controllers\API\ClusterController::class, 'action_list'])->name('api.cluster.list');
    Route::post('/projects/{project_id}/clusters', [App\Http\Controllers\API\ClusterController::class, 'action_add'])->name('api.cluster.add');
    Route::get('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_get'])->name('api.cluster.get');
    Route::patch('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_update'])->name('api.cluster.update');
    Route::delete('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_delete'])->name('api.cluster.delete');

    Route::get('/projects/{project_id}/deployments', [App\Http\Controllers\API\DeploymentController::class, 'action_list'])->name('api.deployment.list');
    Route::post('/projects/{project_id}/deployments', [App\Http\Controllers\API\DeploymentController::class, 'action_add'])->name('api.deployment.add');
    Route::get('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_get'])->name('api.deployment.get');
    Route::patch('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_update'])->name('api.deployment.update');
    Route::delete('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_delete'])->name('api.deployment.delete');
    Route::put('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_put_network_policy'])->name('api.deployment.netpol.put');
    Route::delete('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_delete_network_policy'])->name('api.deployment.netpol.delete');
    Route::patch('/projects/{project_id}/deployments/{deployment_id}/commit/{commit_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_revert_commit'])->name('api.deployment.commit.revert');
});

Route::fallback(function () {
    return Response::generate(404, 'error', 'Not found');
});
