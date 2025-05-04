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
        Schema::create('clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignUuid('project_id')->references('id')->on('projects');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_k8s_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->longText('kubeconfig');
            $table->string('api_url');
            $table->longText('service_account_token');
            $table->string('node_prefix')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_git_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->string('url');
            $table->string('branch');
            $table->longText('credentials');
            $table->string('username');
            $table->string('email');
            $table->string('base_path')->default('/');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->enum('type', ['limit', 'alert']);
            $table->double('cpu')->nullable();
            $table->double('memory')->nullable();
            $table->double('storage')->nullable();
            $table->double('pods')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_namespaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->enum('type', ['utility', 'ingress']);
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cluster_namespaces');
        Schema::dropIfExists('cluster_resources');
        Schema::dropIfExists('cluster_git_credentials');
        Schema::dropIfExists('cluster_k8s_credentials');
        Schema::dropIfExists('clusters');
    }
};
