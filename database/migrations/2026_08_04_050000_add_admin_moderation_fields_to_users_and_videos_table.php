<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('banned_at')->nullable()->after('premium_until');
            $table->string('ban_reason', 500)->nullable()->after('banned_at');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('is_premium');
            $table->unsignedTinyInteger('age_restriction')->default(0)->after('is_featured');
            $table->string('copyright_status', 20)->default('none')->after('age_restriction');
            $table->text('copyright_note')->nullable()->after('copyright_status');
            $table->index(['is_featured', 'status']);
            $table->index(['copyright_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'status']);
            $table->dropIndex(['copyright_status', 'created_at']);
            $table->dropColumn(['is_featured', 'age_restriction', 'copyright_status', 'copyright_note']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['banned_at', 'ban_reason']);
        });
    }
};
