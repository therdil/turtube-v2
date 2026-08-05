<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->string('processing_status', 20)->default('ready')->after('video_path');
            $table->text('processing_error')->nullable()->after('processing_status');
            $table->index('processing_status');
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['processing_status']);
            $table->dropColumn(['processing_status', 'processing_error']);
        });
    }
};
