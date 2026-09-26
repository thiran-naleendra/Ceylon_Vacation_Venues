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
        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->json('featured_image_variants')->nullable()->after('featured_image_path');
        });

        Schema::table('tour_packages', function (Blueprint $table): void {
            $table->index(['status', 'published_at', 'destination'], 'packages_public_destination_index');
            $table->index(['status', 'published_at', 'availability_status', 'sort_order'], 'packages_public_filter_index');
        });

        Schema::table('package_images', function (Blueprint $table): void {
            $table->index(['tour_package_id', 'processing_status', 'sort_order'], 'package_images_ready_order_index');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->index(['status', 'published_at', 'availability_status', 'sort_order'], 'vehicles_public_filter_index');
            $table->index(['status', 'published_at', 'transmission'], 'vehicles_public_transmission_index');
        });

        Schema::table('vehicle_images', function (Blueprint $table): void {
            $table->index(['vehicle_id', 'processing_status', 'sort_order'], 'vehicle_images_ready_order_index');
        });

        Schema::table('gallery_images', function (Blueprint $table): void {
            $table->index(['gallery_album_id', 'status', 'is_visible', 'processing_status', 'sort_order'], 'gallery_images_public_order_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table): void {
            $table->dropIndex('gallery_images_public_order_index');
        });

        Schema::table('vehicle_images', function (Blueprint $table): void {
            $table->dropIndex('vehicle_images_ready_order_index');
        });

        Schema::table('vehicles', function (Blueprint $table): void {
            $table->dropIndex('vehicles_public_filter_index');
            $table->dropIndex('vehicles_public_transmission_index');
        });

        Schema::table('package_images', function (Blueprint $table): void {
            $table->dropIndex('package_images_ready_order_index');
        });

        Schema::table('tour_packages', function (Blueprint $table): void {
            $table->dropIndex('packages_public_destination_index');
            $table->dropIndex('packages_public_filter_index');
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn('featured_image_variants');
        });
    }
};
