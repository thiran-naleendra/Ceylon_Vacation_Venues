<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_categories', function (Blueprint $table): void {
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

        Schema::create('vehicles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_category_id')->constrained('vehicle_categories')->restrictOnDelete();
            $table->string('title');
            $table->string('slug', 180)->unique();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('model_year')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedSmallInteger('seats')->nullable();
            $table->string('transmission', 30)->nullable();
            $table->string('fuel_type', 30)->nullable();
            $table->decimal('rental_rate', 12, 2)->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('rate_unit', 30)->default('day');
            $table->text('rental_terms')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['vehicle_category_id', 'status', 'sort_order']);
            $table->index(['status', 'is_featured', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('vehicle_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->cascadeOnDelete();
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
            $table->index(['vehicle_id', 'sort_order']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_images');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('vehicle_categories');
    }
};
