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
    'api.guard',
    IdentifyProject::class,
])->group(function () {
    Route::get('/activities', [App\Http\Controllers\API\ActivityController::class, 'action_list'])->name('api.activity.list')->middleware('api.permission.guard:activities.view');

    Route::get('/projects', [App\Http\Controllers\API\ProjectController::class, 'action_list'])->name('api.project.list')->middleware('api.permission.guard:projects.view');
    Route::post('/projects', [App\Http\Controllers\API\ProjectController::class, 'action_add'])->name('api.project.add')->middleware('api.permission.guard:projects.add');
    Route::get('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_get'])->name('api.project.get')->middleware('api.permission.guard:projects.view');
    Route::patch('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_update'])->name('api.project.update')->middleware('api.permission.guard:projects.update');
    Route::delete('/projects/{project_id}', [App\Http\Controllers\API\ProjectController::class, 'action_delete'])->name('api.project.delete')->middleware('api.permission.guard:projects.delete');

    Route::get('/templates', [App\Http\Controllers\API\TemplateController::class, 'action_list'])->name('api.template.list')->middleware('api.permission.guard:templates.view');
    Route::post('/templates', [App\Http\Controllers\API\TemplateController::class, 'action_add'])->name('api.template.add')->middleware('api.permission.guard:templates.add');
    Route::post('/templates/import', [App\Http\Controllers\API\TemplateController::class, 'action_import'])->name('api.template.import')->middleware('api.permission.guard:templates.import');
    Route::post('/templates/sync', [App\Http\Controllers\API\TemplateController::class, 'action_sync'])->name('api.template.sync')->middleware('api.permission.guard:templates.sync');
    Route::get('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get'])->name('api.template.get')->middleware('api.permission.guard:templates.view');
    Route::patch('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update'])->name('api.template.update')->middleware('api.permission.guard:templates.update');
    Route::delete('/templates/{template_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete'])->name('api.template.delete')->middleware('api.permission.guard:templates.delete');

    Route::get('/templates/{template_id}/folders', [App\Http\Controllers\API\TemplateController::class, 'action_list_folder'])->name('api.template.folder.list')->middleware('api.permission.guard:templates.folders.view');
    Route::post('/templates/{template_id}/folders', [App\Http\Controllers\API\TemplateController::class, 'action_add_folder'])->name('api.template.folder.add')->middleware('api.permission.guard:templates.folders.add');
    Route::get('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_folder'])->name('api.template.folder.get')->middleware('api.permission.guard:templates.folders.view');
    Route::patch('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_folder'])->name('api.template.folder.update')->middleware('api.permission.guard:templates.folders.update');
    Route::delete('/templates/{template_id}/folders/{folder_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_folder'])->name('api.template.folder.delete')->middleware('api.permission.guard:templates.folders.delete');

    Route::get('/templates/{template_id}/files', [App\Http\Controllers\API\TemplateController::class, 'action_list_file'])->name('api.template.file.list')->middleware('api.permission.guard:templates.files.view');
    Route::post('/templates/{template_id}/files', [App\Http\Controllers\API\TemplateController::class, 'action_add_file'])->name('api.template.file.add')->middleware('api.permission.guard:templates.files.add');
    Route::get('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_file'])->name('api.template.file.get')->middleware('api.permission.guard:templates.files.view');
    Route::patch('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_file'])->name('api.template.file.update')->middleware('api.permission.guard:templates.files.update');
    Route::delete('/templates/{template_id}/files/{file_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_file'])->name('api.template.file.delete')->middleware('api.permission.guard:templates.files.delete');

    Route::get('/templates/{template_id}/fields', [App\Http\Controllers\API\TemplateController::class, 'action_list_field'])->name('api.template.field.list')->middleware('api.permission.guard:templates.fields.view');
    Route::post('/templates/{template_id}/fields', [App\Http\Controllers\API\TemplateController::class, 'action_add_field'])->name('api.template.field.add')->middleware('api.permission.guard:templates.fields.add');
    Route::get('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_field'])->name('api.template.field.get')->middleware('api.permission.guard:templates.fields.view');
    Route::patch('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_field'])->name('api.template.field.update')->middleware('api.permission.guard:templates.fields.update');
    Route::delete('/templates/{template_id}/fields/{field_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_field'])->name('api.template.field.delete')->middleware('api.permission.guard:templates.fields.delete');

    Route::get('/templates/{template_id}/fields/{field_id}/options', [App\Http\Controllers\API\TemplateController::class, 'action_list_option'])->name('api.template.field.option.list')->middleware('api.permission.guard:templates.fields.options.view');
    Route::post('/templates/{template_id}/fields/{field_id}/options', [App\Http\Controllers\API\TemplateController::class, 'action_add_option'])->name('api.template.field.option.add')->middleware('api.permission.guard:templates.fields.options.add');
    Route::get('/templates/{template_id}/fields/{field_id}/options/{option_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_option'])->name('api.template.field.option.get')->middleware('api.permission.guard:templates.fields.options.view');
    Route::patch('/templates/{template_id}/fields/{field_id}/options/{option_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_option'])->name('api.template.field.option.update')->middleware('api.permission.guard:templates.fields.options.update');
    Route::delete('/templates/{template_id}/fields/{field_id}/options/{option_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_option'])->name('api.template.field.option.delete')->middleware('api.permission.guard:templates.fields.options.delete');

    Route::get('/templates/{template_id}/ports', [App\Http\Controllers\API\TemplateController::class, 'action_list_port'])->name('api.template.port.list')->middleware('api.permission.guard:templates.ports.view');
    Route::post('/templates/{template_id}/ports', [App\Http\Controllers\API\TemplateController::class, 'action_add_port'])->name('api.template.port.add')->middleware('api.permission.guard:templates.ports.add');
    Route::get('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_get_port'])->name('api.template.port.get')->middleware('api.permission.guard:templates.ports.view');
    Route::patch('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_update_port'])->name('api.template.port.update')->middleware('api.permission.guard:templates.ports.update');
    Route::delete('/templates/{template_id}/ports/{port_id}', [App\Http\Controllers\API\TemplateController::class, 'action_delete_port'])->name('api.template.port.delete')->middleware('api.permission.guard:templates.ports.delete');

    Route::get('/projects/{project_id}/clusters', [App\Http\Controllers\API\ClusterController::class, 'action_list'])->name('api.cluster.list')->middleware('api.permission.guard:projects.clusters.view');
    Route::post('/projects/{project_id}/clusters', [App\Http\Controllers\API\ClusterController::class, 'action_add'])->name('api.cluster.add')->middleware('api.permission.guard:projects.clusters.add');
    Route::get('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_get'])->name('api.cluster.get')->middleware('api.permission.guard:projects.clusters.view');
    Route::patch('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_update'])->name('api.cluster.update')->middleware('api.permission.guard:projects.clusters.update');
    Route::delete('/projects/{project_id}/clusters/{cluster_id}', [App\Http\Controllers\API\ClusterController::class, 'action_delete'])->name('api.cluster.delete')->middleware('api.permission.guard:projects.clusters.delete');

    Route::get('/projects/{project_id}/deployments', [App\Http\Controllers\API\DeploymentController::class, 'action_list'])->name('api.deployment.list')->middleware('api.permission.guard:projects.deployments.view');
    Route::post('/projects/{project_id}/deployments', [App\Http\Controllers\API\DeploymentController::class, 'action_add'])->name('api.deployment.add')->middleware('api.permission.guard:projects.deployments.add');
    Route::get('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_get'])->name('api.deployment.get')->middleware('api.permission.guard:projects.deployments.view');
    Route::patch('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_update'])->name('api.deployment.update')->middleware('api.permission.guard:projects.deployments.update');
    Route::delete('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_delete'])->name('api.deployment.delete')->middleware('api.permission.guard:projects.deployments.delete');
    Route::patch('/projects/{project_id}/deployments/{deployment_id}/approve', [App\Http\Controllers\API\DeploymentController::class, 'action_approve'])->name('api.deployment.approve')->middleware('api.permission.guard:projects.deployments.approve');
    Route::put('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_put_network_policy'])->name('api.deployment.network-policies.put')->middleware('api.permission.guard:projects.deployments.network-policies.put');
    Route::delete('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_delete_network_policy'])->name('api.deployment.network-policies.delete')->middleware('api.permission.guard:projects.deployments.network-policies.delete');
    Route::patch('/projects/{project_id}/deployments/{deployment_id}/commit/{commit_id}', [App\Http\Controllers\API\DeploymentController::class, 'action_revert_commit'])->name('api.deployment.commit.revert')->middleware('api.permission.guard:projects.deployments.commit.revert');

    Route::get('/users', [App\Http\Controllers\API\UserController::class, 'action_list'])->name('api.user.list')->middleware('api.permission.guard:users.view');
    Route::post('/users', [App\Http\Controllers\API\UserController::class, 'action_add'])->name('api.user.add')->middleware('api.permission.guard:users.add');
    Route::get('/users/{user_id}', [App\Http\Controllers\API\UserController::class, 'action_get'])->name('api.user.get')->middleware('api.permission.guard:users.view');
    Route::patch('/users/{user_id}', [App\Http\Controllers\API\UserController::class, 'action_update'])->name('api.user.update')->middleware('api.permission.guard:users.update');
    Route::delete('/users/{user_id}', [App\Http\Controllers\API\UserController::class, 'action_delete'])->name('api.user.delete')->middleware('api.permission.guard:users.delete');
    Route::post('/users/{user_id}/magic-link', [App\Http\Controllers\API\UserController::class, 'action_magic_link'])->name('api.user.magic-link')->middleware('api.permission.guard:users.magic-link');

    Route::get('/roles', [App\Http\Controllers\API\RoleController::class, 'action_list'])->name('api.role.list')->middleware('api.permission.guard:roles.view');
    Route::post('/roles', [App\Http\Controllers\API\RoleController::class, 'action_add'])->name('api.role.add')->middleware('api.permission.guard:roles.add');
    Route::get('/roles/{role_id}', [App\Http\Controllers\API\RoleController::class, 'action_get'])->name('api.role.get')->middleware('api.permission.guard:roles.view');
    Route::patch('/roles/{role_id}', [App\Http\Controllers\API\RoleController::class, 'action_update'])->name('api.role.update')->middleware('api.permission.guard:roles.update');
    Route::delete('/roles/{role_id}', [App\Http\Controllers\API\RoleController::class, 'action_delete'])->name('api.role.delete')->middleware('api.permission.guard:roles.delete');
});

Route::fallback(function () {
    return Response::generate(404, 'error', 'Not found');
});
