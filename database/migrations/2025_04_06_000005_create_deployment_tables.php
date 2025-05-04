<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignUuid('project_id')->references('id')->on('projects');
            $table->foreignUuid('namespace_id')->nullable()->references('id')->on('namespaces');
            $table->foreignUuid('template_id')->references('id')->on('templates');
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->string('name');
            $table->string('uuid')->unique();
            $table->boolean('paused')->default(false);
            $table->boolean('update')->default(false);
            $table->boolean('delete')->default(false);
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('deployment_updated_at')->nullable();
            $table->timestamp('creation_dispatched_at')->nullable();
            $table->timestamp('update_dispatched_at')->nullable();
            $table->timestamp('deletion_dispatched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->foreignUuid('template_field_id')->references('id')->on('template_fields');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_secret_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->foreignUuid('template_field_id')->references('id')->on('template_fields');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->bigInteger('storage_bytes')->unsigned();
            $table->bigInteger('memory_bytes')->unsigned();
            $table->double('cpu_core_usage')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_limits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->boolean('is_active')->default(false);
            $table->double('memory')->nullable();
            $table->double('cpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('source_deployment_id')->references('id')->on('deployments');
            $table->foreignUuid('target_deployment_id')->references('id')->on('deployments');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_commits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->string('hash');
            $table->longText('message');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_commit_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_commit_id')->references('id')->on('deployment_commits');
            $table->foreignUuid('deployment_data_id')->references('id')->on('deployment_data');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('deployment_commit_secret_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_commit_id')->references('id')->on('deployment_commits');
            $table->foreignUuid('deployment_secret_data_id')->references('id')->on('deployment_secret_data');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('reserved_ports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('deployment_id')->references('id')->on('deployments');
            $table->string('group')->default('services');
            $table->string('claim')->nullable();
            $table->integer('port');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('reserved_ports');
        Schema::dropIfExists('deployment_commit_secret_data');
        Schema::dropIfExists('deployment_commit_data');
        Schema::dropIfExists('deployment_commits');
        Schema::dropIfExists('deployment_links');
        Schema::dropIfExists('deployment_limits');
        Schema::dropIfExists('deployment_metrics');
        Schema::dropIfExists('deployment_secret_data');
        Schema::dropIfExists('deployment_data');
        Schema::dropIfExists('deployments');
    }
};
