<?php

declare(strict_types=1);

use App\Http\Middleware\CheckVersion;
use App\Http\Middleware\IdentifyProject;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/projects');
})->name('home');

Auth::routes([
    'register' => false,
    'verify'   => false,
]);

Route::get('/auth/social/{provider}/redirect', [App\Http\Controllers\Auth\SocialiteController::class, 'redirect'])->name('auth.social.redirect');
Route::get('/auth/social/{provider}/callback', [App\Http\Controllers\Auth\SocialiteController::class, 'handleCallback'])->name('auth.social.callback');

Route::middleware([
    'auth',
    IdentifyProject::class,
    CheckVersion::class,
])->group(function () {
    Route::get('/projects', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.index')->middleware('ui.permission.guard:projects.view');
    Route::get('/projects/add', [App\Http\Controllers\ProjectController::class, 'page_add'])->name('project.add')->middleware('ui.permission.guard:projects.add');
    Route::post('/projects/add', [App\Http\Controllers\ProjectController::class, 'action_add'])->name('project.add.action')->middleware('ui.permission.guard:projects.add');
    Route::get('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'page_update'])->name('project.update')->middleware('ui.permission.guard:projects.update');
    Route::post('/projects/{project_id}/update', [App\Http\Controllers\ProjectController::class, 'action_update'])->name('project.update.action')->middleware('ui.permission.guard:projects.update');
    Route::get('/projects/{project_id}/delete', [App\Http\Controllers\ProjectController::class, 'action_delete'])->name('project.delete.action')->middleware('ui.permission.guard:projects.delete');
    Route::get('/projects/{project_id}', [App\Http\Controllers\ProjectController::class, 'page_index'])->name('project.details')->middleware('ui.permission.guard:projects.view');

    Route::get('/templates', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.index')->middleware('ui.permission.guard:templates.view');
    Route::get('/templates/add', [App\Http\Controllers\TemplateController::class, 'page_add'])->name('template.add')->middleware('ui.permission.guard:templates.add');
    Route::post('/templates/add', [App\Http\Controllers\TemplateController::class, 'action_add'])->name('template.add.action')->middleware('ui.permission.guard:templates.add');
    Route::get('/templates/import', [App\Http\Controllers\TemplateController::class, 'page_import'])->name('template.import')->middleware('ui.permission.guard:templates.import');
    Route::post('/templates/import', [App\Http\Controllers\TemplateController::class, 'action_import'])->name('template.import.action')->middleware('ui.permission.guard:templates.import');
    Route::get('/templates/sync', [App\Http\Controllers\TemplateController::class, 'page_sync'])->name('template.sync')->middleware('ui.permission.guard:templates.sync');
    Route::post('/templates/sync', [App\Http\Controllers\TemplateController::class, 'action_sync'])->name('template.sync.action')->middleware('ui.permission.guard:templates.sync');
    Route::get('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update'])->name('template.update')->middleware('ui.permission.guard:templates.update');
    Route::post('/templates/{template_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update'])->name('template.update.action')->middleware('ui.permission.guard:templates.update');
    Route::get('/templates/{template_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete'])->name('template.delete.action')->middleware('ui.permission.guard:templates.delete');
    Route::get('/templates/{template_id}', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.details')->middleware('ui.permission.guard:templates.view');

    Route::get('/templates/{template_id}/folder/add', [App\Http\Controllers\TemplateController::class, 'page_add_folder'])->name('template.folder.add')->middleware('ui.permission.guard:templates.folders.add');
    Route::post('/templates/{template_id}/folder/add', [App\Http\Controllers\TemplateController::class, 'action_add_folder'])->name('template.folder.add.action')->middleware('ui.permission.guard:templates.folders.add');
    Route::get('/templates/{template_id}/folder/{folder_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_folder'])->name('template.folder.update')->middleware('ui.permission.guard:templates.folders.update');
    Route::post('/templates/{template_id}/folder/{folder_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_folder'])->name('template.folder.update.action')->middleware('ui.permission.guard:templates.folders.update');
    Route::get('/templates/{template_id}/folder/{folder_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_folder'])->name('template.folder.delete.action')->middleware('ui.permission.guard:templates.folders.delete');
    Route::get('/templates/{template_id}/file/add', [App\Http\Controllers\TemplateController::class, 'page_add_file'])->name('template.file.add')->middleware('ui.permission.guard:templates.files.add');
    Route::post('/templates/{template_id}/file/add', [App\Http\Controllers\TemplateController::class, 'action_add_file'])->name('template.file.add.action')->middleware('ui.permission.guard:templates.files.add');
    Route::get('/templates/{template_id}/file/{file_id}', [App\Http\Controllers\TemplateController::class, 'page_index'])->name('template.details_file')->middleware('ui.permission.guard:templates.files.view');
    Route::get('/templates/{template_id}/file/{file_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_file'])->name('template.file.update')->middleware('ui.permission.guard:templates.files.update');
    Route::post('/templates/{template_id}/file/{file_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_file'])->name('template.file.update.action')->middleware('ui.permission.guard:templates.files.update');
    Route::get('/templates/{template_id}/file/{file_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_file'])->name('template.file.delete.action')->middleware('ui.permission.guard:templates.files.delete');

    Route::get('/templates/{template_id}/field/add', [App\Http\Controllers\TemplateController::class, 'page_add_field'])->name('template.field.add')->middleware('ui.permission.guard:templates.fields.add');
    Route::post('/templates/{template_id}/field/add', [App\Http\Controllers\TemplateController::class, 'action_add_field'])->name('template.field.add.action')->middleware('ui.permission.guard:templates.fields.add');
    Route::get('/templates/{template_id}/field/{field_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_field'])->name('template.field.update')->middleware('ui.permission.guard:templates.fields.update');
    Route::post('/templates/{template_id}/field/{field_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_field'])->name('template.field.update.action')->middleware('ui.permission.guard:templates.fields.update');
    Route::get('/templates/{template_id}/field/{field_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_field'])->name('template.field.delete.action')->middleware('ui.permission.guard:templates.fields.delete');
    Route::get('/templates/{template_id}/field/{field_id}/option/add', [App\Http\Controllers\TemplateController::class, 'page_add_option'])->name('template.field.option.add')->middleware('ui.permission.guard:templates.fields.options.add');
    Route::post('/templates/{template_id}/field/{field_id}/option/add', [App\Http\Controllers\TemplateController::class, 'action_add_option'])->name('template.field.option.add.action')->middleware('ui.permission.guard:templates.fields.options.add');
    Route::get('/templates/{template_id}/field/{field_id}/option/{option_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_option'])->name('template.field.option.update')->middleware('ui.permission.guard:templates.fields.options.update');
    Route::post('/templates/{template_id}/field/{field_id}/option/{option_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_option'])->name('template.field.option.update.action')->middleware('ui.permission.guard:templates.fields.options.update');
    Route::get('/templates/{template_id}/field/{field_id}/option/{option_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_option'])->name('template.field.option.delete.action')->middleware('ui.permission.guard:templates.fields.options.delete');
    Route::post('/ai/tool/action', [App\Http\Controllers\AiController::class, 'action_tool_action'])->name('ai.tool.action')->middleware('ui.permission.guard:ai.tool.action');

    Route::get('/templates/{template_id}/port/add', [App\Http\Controllers\TemplateController::class, 'page_add_port'])->name('template.port.add')->middleware('ui.permission.guard:templates.ports.add');
    Route::post('/templates/{template_id}/port/add', [App\Http\Controllers\TemplateController::class, 'action_add_port'])->name('template.port.add.action')->middleware('ui.permission.guard:templates.ports.add');
    Route::get('/templates/{template_id}/port/{port_id}/update', [App\Http\Controllers\TemplateController::class, 'page_update_port'])->name('template.port.update')->middleware('ui.permission.guard:templates.ports.update');
    Route::post('/templates/{template_id}/port/{port_id}/update', [App\Http\Controllers\TemplateController::class, 'action_update_port'])->name('template.port.update.action')->middleware('ui.permission.guard:templates.ports.update');
    Route::get('/templates/{template_id}/port/{port_id}/delete', [App\Http\Controllers\TemplateController::class, 'action_delete_port'])->name('template.port.delete.action')->middleware('ui.permission.guard:templates.ports.delete');

    Route::get('/projects/{project_id}/clusters', [App\Http\Controllers\ClusterController::class, 'page_index'])->name('cluster.index')->middleware('ui.permission.guard:projects.clusters.view');
    Route::get('/projects/{project_id}/clusters/add', [App\Http\Controllers\ClusterController::class, 'page_add'])->name('cluster.add')->middleware('ui.permission.guard:projects.clusters.add');
    Route::post('/projects/{project_id}/clusters/add', [App\Http\Controllers\ClusterController::class, 'action_add'])->name('cluster.add.action')->middleware('ui.permission.guard:projects.clusters.add');
    Route::get('/projects/{project_id}/clusters/{cluster_id}/update', [App\Http\Controllers\ClusterController::class, 'page_update'])->name('cluster.update')->middleware('ui.permission.guard:projects.clusters.update');
    Route::post('/projects/{project_id}/clusters/{cluster_id}/update', [App\Http\Controllers\ClusterController::class, 'action_update'])->name('cluster.update.action')->middleware('ui.permission.guard:projects.clusters.update');
    Route::get('/projects/{project_id}/clusters/{cluster_id}/delete', [App\Http\Controllers\ClusterController::class, 'action_delete'])->name('cluster.delete.action')->middleware('ui.permission.guard:projects.clusters.delete');

    Route::get('/projects/{project_id}/deployments', [App\Http\Controllers\DeploymentController::class, 'page_index'])->name('deployment.index')->middleware('ui.permission.guard:projects.deployments.view');
    Route::get('/projects/{project_id}/deployments/add', [App\Http\Controllers\DeploymentController::class, 'page_add'])->name('deployment.add')->middleware('ui.permission.guard:projects.deployments.add');
    Route::post('/projects/{project_id}/deployments/add', [App\Http\Controllers\DeploymentController::class, 'action_add'])->name('deployment.add.action')->middleware('ui.permission.guard:projects.deployments.add');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/update', [App\Http\Controllers\DeploymentController::class, 'page_update'])->name('deployment.update')->middleware('ui.permission.guard:projects.deployments.update');
    Route::post('/projects/{project_id}/deployments/{deployment_id}/update', [App\Http\Controllers\DeploymentController::class, 'action_update'])->name('deployment.update.action')->middleware('ui.permission.guard:projects.deployments.update');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/delete', [App\Http\Controllers\DeploymentController::class, 'action_delete'])->name('deployment.delete.action')->middleware('ui.permission.guard:projects.deployments.delete');
    Route::post('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}/put', [App\Http\Controllers\DeploymentController::class, 'action_put_network_policy'])->name('deployment.network-policies.put.action')->middleware('ui.permission.guard:projects.deployments.network-policies.put');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/network-policy/{network_policy_id}/delete', [App\Http\Controllers\DeploymentController::class, 'action_delete_network_policy'])->name('deployment.network-policies.delete.action')->middleware('ui.permission.guard:projects.deployments.network-policies.delete');
    Route::get('/projects/{project_id}/deployments/{deployment_id}/commit/{commit_id}/revert', [App\Http\Controllers\DeploymentController::class, 'action_revert_commit'])->name('deployment.commit.revert.action')->middleware('ui.permission.guard:projects.deployments.commits.revert');
    Route::get('/projects/{project_id}/deployments/{deployment_id}', [App\Http\Controllers\DeploymentController::class, 'page_index'])->name('deployment.details')->middleware('ui.permission.guard:projects.deployments.view');

    Route::get('/users', [App\Http\Controllers\UserController::class, 'page_index'])->name('user.index')->middleware('ui.permission.guard:users.view');
    Route::get('/users/add', [App\Http\Controllers\UserController::class, 'page_add'])->name('user.add')->middleware('ui.permission.guard:users.add');
    Route::post('/users/add', [App\Http\Controllers\UserController::class, 'action_add'])->name('user.add.action')->middleware('ui.permission.guard:users.add');
    Route::get('/users/{user_id}/update', [App\Http\Controllers\UserController::class, 'page_update'])->name('user.update')->middleware('ui.permission.guard:users.update');
    Route::post('/users/{user_id}/update', [App\Http\Controllers\UserController::class, 'action_update'])->name('user.update.action')->middleware('ui.permission.guard:users.update');
    Route::get('/users/{user_id}/delete', [App\Http\Controllers\UserController::class, 'action_delete'])->name('user.delete.action')->middleware('ui.permission.guard:users.delete');

    Route::get('/roles', [App\Http\Controllers\RoleController::class, 'page_index'])->name('role.index')->middleware('ui.permission.guard:roles.view');
    Route::get('/roles/add', [App\Http\Controllers\RoleController::class, 'page_add'])->name('role.add')->middleware('ui.permission.guard:roles.add');
    Route::post('/roles/add', [App\Http\Controllers\RoleController::class, 'action_add'])->name('role.add.action')->middleware('ui.permission.guard:roles.add');
    Route::get('/roles/{role_id}/update', [App\Http\Controllers\RoleController::class, 'page_update'])->name('role.update')->middleware('ui.permission.guard:roles.update');
    Route::post('/roles/{role_id}/update', [App\Http\Controllers\RoleController::class, 'action_update'])->name('role.update.action')->middleware('ui.permission.guard:roles.update');
    Route::get('/roles/{role_id}/delete', [App\Http\Controllers\RoleController::class, 'action_delete'])->name('role.delete.action')->middleware('ui.permission.guard:roles.delete');

    Route::get('/updated', function () {
        if (request()->attributes->has('update')) {
            return redirect()->back()->with('success', __('Kublade is still outdated. Please update to the latest version.'));
        }

        return redirect()->back()->with('success', __('Kublade is now up to date.'));
    })->name('updated');
});

Route::get('/switch-color-mode', function () {
    return redirect()->back()->withCookie(cookie()->make('theme', request()->cookie('theme') === 'dark' ? 'light' : 'dark', 525960));
})->name('switch-color-mode')->middleware('ui.permission.guard:dark-mode');
