<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('page_key', 100)->unique();
            $table->string('title');
            $table->string('slug', 180)->unique();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->string('template', 50)->default('standard');
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['status', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('section_key', 100);
            $table->string('section_type', 50);
            $table->json('content');
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['page_id', 'section_key']);
            $table->index(['page_id', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['status', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->restrictOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug', 180)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('featured_image_alt')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->boolean('is_featured')->default(false);
            $table->index(['blog_category_id', 'status', 'published_at']);
            $table->index(['status', 'is_featured', 'published_at']);
            $table->timestamps();
        });

        Schema::create('gallery_albums', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug', 180)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['status', 'is_featured', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('gallery_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('gallery_album_id')->constrained('gallery_albums')->cascadeOnDelete();
            $table->string('disk', 50)->default('public');
            $table->string('path');
            $table->string('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->json('variants')->nullable();
            $table->string('processing_status', 20)->default('pending');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->index(['gallery_album_id', 'is_visible', 'sort_order']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
        Schema::dropIfExists('gallery_albums');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
    }
};
