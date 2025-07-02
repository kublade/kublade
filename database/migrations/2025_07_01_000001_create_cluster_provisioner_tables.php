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
        Schema::create('cluster_provisioner_meta', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cluster_id')->references('id')->on('clusters');
            $table->string('key');
            $table->longText('value');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cluster_provisioner_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->references('id')->on('projects');
            $table->string('provisioner');
            $table->longText('config');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('cluster_provisioner_configs');
        Schema::dropIfExists('cluster_provisioner_meta');
    }
};
