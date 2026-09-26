<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('slug', 180)->unique();
            $t->text('description')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['status', 'sort_order']);
        });
        Schema::create('amenities', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            $t->string('slug', 180)->unique();
            $t->text('description')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['is_active', 'sort_order']);
        });
        Schema::create('properties', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('property_type_id')->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('slug', 180)->unique();
            $t->string('short_description', 500)->nullable();
            $t->longText('description')->nullable();
            $t->string('location')->nullable();
            $t->text('address_description')->nullable();
            $t->decimal('price', 12, 2)->nullable();
            $t->char('currency', 3)->default('USD');
            $t->string('pricing_unit', 40)->default('per_night');
            $t->unsignedSmallInteger('bedrooms')->nullable();
            $t->decimal('bathrooms', 4, 1)->nullable();
            $t->unsignedSmallInteger('max_guests')->nullable();
            $t->text('beds_details')->nullable();
            $t->text('availability_information')->nullable();
            $t->time('check_in_time')->nullable();
            $t->time('check_out_time')->nullable();
            $t->string('status', 20)->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->boolean('is_featured')->default(false);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['status', 'sort_order']);
            $t->index(['property_type_id', 'status']);
            $t->index(['is_featured', 'status']);
            $t->index('location');
        });
        Schema::create('amenity_property', function (Blueprint $t): void {
            $t->foreignId('amenity_id')->constrained()->restrictOnDelete();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->primary(['amenity_id', 'property_id']);
        });
        Schema::create('property_images', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('property_id')->constrained()->cascadeOnDelete();
            $t->string('disk', 40)->default('public');
            $t->string('path');
            $t->string('alt_text');
            $t->text('caption')->nullable();
            $t->string('mime_type', 100);
            $t->unsignedBigInteger('file_size');
            $t->unsignedInteger('width')->nullable();
            $t->unsignedInteger('height')->nullable();
            $t->json('variants')->nullable();
            $t->string('processing_status', 20)->default('ready');
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
            $t->index(['property_id', 'processing_status', 'sort_order']);
        });
        Schema::create('property_inquiry_details', function (Blueprint $t): void {
            $t->id();
            $t->foreignId('inquiry_id')->unique()->constrained()->cascadeOnDelete();
            $t->foreignId('property_id')->constrained()->restrictOnDelete();
            $t->string('property_name_snapshot');
            $t->date('check_in_date');
            $t->date('check_out_date');
            $t->unsignedSmallInteger('guests');
            $t->timestamps();
            $t->index(['property_id', 'check_in_date']);
        });
        Schema::table('seo_metadata', function (Blueprint $t): void {
            $t->foreignId('property_id')->nullable()->unique()->constrained()->cascadeOnDelete();
        });
        $this->seoConstraint(true);
        $now = now();
        DB::table('property_types')->insert([['name' => 'Villa', 'slug' => 'villa', 'status' => 'published', 'published_at' => $now, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now], ['name' => 'House', 'slug' => 'house', 'status' => 'published', 'published_at' => $now, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now]]);
        foreach (['WiFi', 'Air Conditioning', 'Swimming Pool', 'Kitchen', 'Parking', 'Hot Water', 'TV', 'Garden', 'Beach Access'] as $order => $name) {
            DB::table('amenities')->insert(['name' => $name, 'slug' => str($name)->slug(), 'sort_order' => $order, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        $this->seoConstraint(false, true);
        Schema::table('seo_metadata', fn (Blueprint $t) => $t->dropConstrainedForeignId('property_id'));
        $this->seoConstraint(false);
        Schema::dropIfExists('property_inquiry_details');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('amenity_property');
        Schema::dropIfExists('properties');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('property_types');
    }

    private function seoConstraint(bool $property, bool $dropOnly = false): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite') {
            DB::unprepared('DROP TRIGGER IF EXISTS seo_metadata_owner_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS seo_metadata_owner_update');
            if ($dropOnly) {
                return;
            }
            $owners = ['page_id', 'tour_package_id', 'vehicle_category_id', 'vehicle_id', 'blog_category_id', 'blog_post_id', 'gallery_album_id'];
            if ($property) {
                $owners[] = 'property_id';
            }
            $sum = collect($owners)->map(fn (string $column): string => "(NEW.{$column} IS NOT NULL)")->implode(' + ');
            DB::unprepared("CREATE TRIGGER seo_metadata_owner_insert BEFORE INSERT ON seo_metadata WHEN ({$sum}) <> 1 BEGIN SELECT RAISE(ABORT, 'SEO metadata requires exactly one owner'); END");
            DB::unprepared("CREATE TRIGGER seo_metadata_owner_update BEFORE UPDATE ON seo_metadata WHEN ({$sum}) <> 1 BEGIN SELECT RAISE(ABORT, 'SEO metadata requires exactly one owner'); END");

            return;
        }
        $version = (string) DB::selectOne('SELECT VERSION() AS version')->version;
        $dropKeyword = str_contains($version, 'MariaDB') ? 'CONSTRAINT' : 'CHECK';
        DB::statement("ALTER TABLE seo_metadata DROP {$dropKeyword} seo_metadata_exactly_one_owner");
        if (! $dropOnly) {
            $extra = $property ? ' + (property_id IS NOT NULL)' : '';
            DB::statement("ALTER TABLE seo_metadata ADD CONSTRAINT seo_metadata_exactly_one_owner CHECK ((page_id IS NOT NULL) + (tour_package_id IS NOT NULL) + (vehicle_category_id IS NOT NULL) + (vehicle_id IS NOT NULL) + (blog_category_id IS NOT NULL) + (blog_post_id IS NOT NULL) + (gallery_album_id IS NOT NULL){$extra} = 1)");
        }
    }
};
