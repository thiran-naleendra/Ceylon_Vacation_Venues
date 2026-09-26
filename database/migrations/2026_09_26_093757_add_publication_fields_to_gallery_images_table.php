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
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->string('title')->after('gallery_album_id');
            $table->string('status', 20)->default('draft')->after('is_visible');
            $table->timestamp('published_at')->nullable()->after('status');
            $table->index(['status', 'published_at', 'sort_order']);
            $table->index(['gallery_album_id', 'status', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropIndex(['status', 'published_at', 'sort_order']);
            $table->dropIndex(['gallery_album_id', 'status', 'sort_order']);
            $table->dropColumn(['title', 'status', 'published_at']);
        });
    }
};
