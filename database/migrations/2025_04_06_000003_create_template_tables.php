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
        Schema::create('templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_ports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->references('id')->on('templates');
            $table->string('group')->default('services');
            $table->string('claim')->nullable();
            $table->integer('preferred_port')->nullable();
            $table->boolean('random')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->references('id')->on('templates');
            $table->enum('type', [
                'input_text',
                'input_number',
                'input_range',
                'input_radio',
                'input_radio_image',
                'input_checkbox',
                'input_hidden',
                'select',
                'textarea',
            ]);
            $table->boolean('required')->default(true);
            $table->boolean('secret')->default(false);
            $table->string('label');
            $table->string('key');
            $table->string('value')->nullable();
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->double('step')->nullable();
            $table->boolean('set_on_create')->default(true);
            $table->boolean('set_on_update')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_field_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_field_id')->references('id')->on('template_fields');
            $table->string('label');
            $table->string('value');
            $table->boolean('default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('template_field_options');
        Schema::dropIfExists('template_fields');
        Schema::dropIfExists('templates');
    }
};
