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
        Schema::create('nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->string('uuid')->unique();
            $table->string('api_version');
            $table->string('name');
            $table->bigInteger('resource_version')->unsigned();
            $table->timestamp('node_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_specs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('provider_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_spec_cidrs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_spec_id')->references('id')->on('node_specs');
            $table->string('cidr');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_capacities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->bigInteger('cpu')->unsigned();
            $table->bigInteger('ephemeral_storage')->unsigned();
            $table->bigInteger('hugepages_1gi')->unsigned();
            $table->bigInteger('hugepages_2mi')->unsigned();
            $table->bigInteger('memory')->unsigned();
            $table->bigInteger('pods')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_allocatables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->bigInteger('cpu')->unsigned();
            $table->bigInteger('ephemeral_storage')->unsigned();
            $table->bigInteger('hugepages_1gi')->unsigned();
            $table->bigInteger('hugepages_2mi')->unsigned();
            $table->bigInteger('memory')->unsigned();
            $table->bigInteger('pods')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('type');
            $table->string('status');
            $table->timestamp('last_heartbeat_time');
            $table->timestamp('last_transition_time');
            $table->string('reason');
            $table->string('message');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('type');
            $table->string('address');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_daemon_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('name');
            $table->integer('port');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_node_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('machine_id');
            $table->string('system_uuid');
            $table->string('boot_id');
            $table->string('kernel_version');
            $table->string('os_image');
            $table->string('container_runtime_version');
            $table->string('kubelet_version');
            $table->string('kube_proxy_version');
            $table->string('operating_system');
            $table->string('architecture');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->bigInteger('size_bytes')->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_image_names', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_image_id')->references('id')->on('node_status_images');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_volume_uses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_status_volume_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('name');
            $table->string('device_path');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('node_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->string('cpu_usage');
            $table->string('memory_usage');
            $table->timestamp('metric_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('namespaces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->string('uuid')->unique();
            $table->string('api_version');
            $table->string('name');
            $table->bigInteger('resource_version')->unsigned();
            $table->timestamp('namespace_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('namespace_id')->references('id')->on('namespaces');
            $table->string('api_version');
            $table->string('name');
            $table->bigInteger('resource_version')->unsigned();
            $table->timestamp('pod_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pod_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pod_id')->references('id')->on('pods');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pod_metric_containers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pod_metric_id')->references('id')->on('pod_metrics');
            $table->string('name');
            $table->string('cpu_usage');
            $table->string('memory_usage');
            $table->timestamp('metric_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pod_specs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pod_id')->references('id')->on('pods');
            $table->string('restart_policy');
            $table->bigInteger('termination_grace_period_seconds')->unsigned();
            $table->string('dns_policy');
            $table->string('service_account_name')->nullable();
            $table->string('service_account')->nullable();
            $table->string('node_name')->nullable();
            $table->string('scheduler_name');
            $table->bigInteger('priority')->unsigned();
            $table->boolean('enable_service_links');
            $table->string('preemption_policy');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('persistent_volumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('namespace_id')->references('id')->on('namespaces');
            $table->string('name');
            $table->string('uuid');
            $table->string('resource_version');
            $table->timestamp('volume_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('persistent_volume_specs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('persistent_volume_id')->references('id')->on('persistent_volumes');
            $table->string('capacity');
            $table->string('driver');
            $table->string('volume_handle');
            $table->string('filesystem_type');
            $table->string('claim_kind');
            $table->string('claim_namespace');
            $table->string('claim_name');
            $table->string('claim_uuid');
            $table->string('claim_api_version');
            $table->bigInteger('claim_resource_version')->unsigned();
            $table->string('persistent_volume_reclaim_policy');
            $table->string('volume_mode');
            $table->string('phase');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pod_volumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pod_id')->references('id')->on('pods');
            $table->uuid('persistent_volume_id');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('container_advisory_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('namespace_id')->references('id')->on('namespaces');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->foreignUuid('pod_id')->references('id')->on('pods');
            $table->string('key');
            $table->string('interface')->nullable();
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pod_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pod_id')->references('id')->on('pods');
            $table->longText('logs');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('namespace_id')->references('id')->on('namespaces');
            $table->string('uuid');
            $table->string('name');
            $table->string('public_ip')->nullable();
            $table->bigInteger('resource_version')->unsigned();
            $table->timestamp('service_created_at');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('service_ports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('service_id')->references('id')->on('services');
            $table->string('name');
            $table->string('protocol');
            $table->integer('port');
            $table->string('target_port');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_capacities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('storage');
            $table->double('memory');
            $table->double('pods');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('storage');
            $table->double('memory');
            $table->double('pods');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_utilizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('storage');
            $table->double('memory');
            $table->double('pods');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_nodes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->foreignUuid('node_id')->references('id')->on('nodes');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_node_capacities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('memory');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_node_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('memory');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_metric_node_utilizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_metric_id')->references('id')->on('cluster_metrics');
            $table->double('cpu');
            $table->double('memory');
            $table->double('gpu')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cluster_metric_node_utilizations');
        Schema::dropIfExists('cluster_metric_node_usages');
        Schema::dropIfExists('cluster_metric_node_capacities');
        Schema::dropIfExists('cluster_metric_nodes');
        Schema::dropIfExists('cluster_metric_utilizations');
        Schema::dropIfExists('cluster_metric_usages');
        Schema::dropIfExists('cluster_metric_capacities');
        Schema::dropIfExists('cluster_metrics');
        Schema::dropIfExists('service_ports');
        Schema::dropIfExists('services');
        Schema::dropIfExists('pod_logs');
        Schema::dropIfExists('container_advisory_metrics');
        Schema::dropIfExists('pod_volumes');
        Schema::dropIfExists('persistent_volume_specs');
        Schema::dropIfExists('persistent_volumes');
        Schema::dropIfExists('pod_specs');
        Schema::dropIfExists('pod_metric_containers');
        Schema::dropIfExists('pod_metrics');
        Schema::dropIfExists('pods');
        Schema::dropIfExists('namespaces');
        Schema::dropIfExists('node_metrics');
        Schema::dropIfExists('node_status_volume_attachments');
        Schema::dropIfExists('node_status_volume_uses');
        Schema::dropIfExists('node_status_image_names');
        Schema::dropIfExists('node_status_images');
        Schema::dropIfExists('node_status_node_infos');
        Schema::dropIfExists('node_status_daemon_endpoints');
        Schema::dropIfExists('node_status_addresses');
        Schema::dropIfExists('node_status_conditions');
        Schema::dropIfExists('node_status_allocatables');
        Schema::dropIfExists('node_status_capacities');
        Schema::dropIfExists('node_spec_cidrs');
        Schema::dropIfExists('node_specs');
        Schema::dropIfExists('nodes');
    }
};
