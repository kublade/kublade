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
        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->string('key')->nullable()->after('content');
            $table->boolean('protected')->default(false)->after('key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('ai_chat_messages', function (Blueprint $table) {
            $table->dropColumn('protected');
            $table->dropColumn('key');
        });
    }
};
