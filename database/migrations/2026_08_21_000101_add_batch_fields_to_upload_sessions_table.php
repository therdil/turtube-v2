<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('upload_sessions', function (Blueprint $table): void {
            $table->foreignId('batch_id')
                ->nullable()
                ->after('user_id')
                ->constrained('upload_batches')
                ->cascadeOnDelete();
            $table->string('kind', 20)->nullable()->after('extension');

            $table->index(['batch_id', 'status']);
            $table->unique(['batch_id', 'kind']);
        });
    }

    public function down(): void
    {
        Schema::table('upload_sessions', function (Blueprint $table): void {
            $table->dropUnique(['batch_id', 'kind']);
            $table->dropIndex(['batch_id', 'status']);
            $table->dropConstrainedForeignId('batch_id');
            $table->dropColumn('kind');
        });
    }
};
