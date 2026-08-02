<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('channel_name')
                ->nullable()
                ->after('name');

            $table->text('channel_description')
                ->nullable()
                ->after('channel_name');

            $table->string('avatar')
                ->nullable()
                ->after('channel_description');

            $table->string('banner')
                ->nullable()
                ->after('avatar');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'channel_name',
                'channel_description',
                'avatar',
                'banner',
            ]);

        });
    }
};