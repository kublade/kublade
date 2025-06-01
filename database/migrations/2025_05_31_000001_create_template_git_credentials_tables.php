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
        Schema::create('template_git_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->references('id')->on('templates');
            $table->string('url');
            $table->string('branch');
            $table->longText('credentials')->nullable();
            $table->string('username');
            $table->string('email');
            $table->string('base_path')->default('/');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('template_git_credentials');
    }
};
