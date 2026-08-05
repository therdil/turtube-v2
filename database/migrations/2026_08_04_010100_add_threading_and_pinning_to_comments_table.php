<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')
                ->nullable()
                ->after('user_id')
                ->constrained('comments')
                ->cascadeOnDelete();
            $table->boolean('is_pinned')->default(false)->after('comment');
            $table->index(['video_id', 'parent_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['video_id', 'parent_id', 'is_pinned']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn('is_pinned');
        });
    }
};
