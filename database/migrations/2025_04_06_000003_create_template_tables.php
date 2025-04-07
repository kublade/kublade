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
            $table->integer('reserved_ports')->default(0);
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
            $table->string('label');
            $table->string('key');
            $table->string('value')->nullable();
            $table->double('amount')->nullable();
            $table->double('min')->nullable();
            $table->double('max')->nullable();
            $table->double('step')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('template_field_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_field_id')->references('id')->on('template_fields');
            $table->string('label');
            $table->string('value');
            $table->double('amount')->nullable();
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
