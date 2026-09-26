<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug', 180)->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('duration_nights')->nullable();
            $table->decimal('starting_price', 12, 2)->nullable();
            $table->char('currency', 3)->default('USD');
            $table->string('price_basis', 30)->default('per_person');
            $table->text('inclusions')->nullable();
            $table->text('exclusions')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->index(['status', 'published_at']);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['status', 'is_featured', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('package_itineraries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('overnight_location')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->unique(['tour_package_id', 'day_number']);
            $table->index(['tour_package_id', 'sort_order']);
            $table->timestamps();
        });

        Schema::create('package_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_package_id')->constrained('tour_packages')->cascadeOnDelete();
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
            $table->index(['tour_package_id', 'sort_order']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_images');
        Schema::dropIfExists('package_itineraries');
        Schema::dropIfExists('tour_packages');
    }
};
