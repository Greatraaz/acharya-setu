<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            if (! Schema::hasColumn('channels', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('type');
            }
            if (! Schema::hasColumn('channels', 'category')) {
                $table->string('category')->nullable()->after('type');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (! Schema::hasColumn('messages', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('body');
            }
            if (! Schema::hasColumn('messages', 'liked_by')) {
                $table->json('liked_by')->nullable()->after('likes_count');
            }
        });

        Schema::table('channel_members', function (Blueprint $table) {
            if (! Schema::hasColumn('channel_members', 'last_read_at')) {
                $table->timestamp('last_read_at')->nullable()->after('role');
            }
            if (! Schema::hasColumn('channel_members', 'muted')) {
                $table->boolean('muted')->default(false)->after('last_read_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('channels', function (Blueprint $table) {
            if (Schema::hasColumn('channels', 'is_active')) {
                $table->dropColumn('is_active');
            }
            if (Schema::hasColumn('channels', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'likes_count')) {
                $table->dropColumn('likes_count');
            }
            if (Schema::hasColumn('messages', 'liked_by')) {
                $table->dropColumn('liked_by');
            }
        });

        Schema::table('channel_members', function (Blueprint $table) {
            if (Schema::hasColumn('channel_members', 'last_read_at')) {
                $table->dropColumn('last_read_at');
            }
            if (Schema::hasColumn('channel_members', 'muted')) {
                $table->dropColumn('muted');
            }
        });
    }
};
