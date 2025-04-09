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
        Schema::create('template_directories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->references('id')->on('templates');
            $table->foreignUuid('parent_id')->nullable()->references('id')->on('template_directories');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_directory_id')->nullable()->references('id')->on('template_directories');
            $table->string('name');
            $table->string('mime_type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('template_files');
        Schema::dropIfExists('template_directories');
    }
};
