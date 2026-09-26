<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $version = DB::selectOne('SELECT VERSION() AS version')->version;
            preg_match('/(\d+\.\d+\.\d+)(?=[^0-9]*-MariaDB|[^0-9]*$)/', $version, $matches);
            $minimum = str_contains($version, 'MariaDB') ? '10.2.1' : '8.0.16';
            $numericVersion = $matches[1] ?? preg_replace('/[^0-9.].*$/', '', $version);

            if (version_compare($numericVersion, $minimum, '<')) {
                throw new RuntimeException('SEO integrity requires MySQL 8.0.16+ or MariaDB 10.2.1+.');
            }
        } elseif ($driver !== 'sqlite') {
            throw new RuntimeException('This schema supports MySQL, MariaDB, and SQLite testing.');
        }

        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->nullable()->unique()->constrained('pages')->cascadeOnDelete();
            $table->foreignId('tour_package_id')->nullable()->unique()->constrained('tour_packages')->cascadeOnDelete();
            $table->foreignId('vehicle_category_id')->nullable()->unique()->constrained('vehicle_categories')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->unique()->constrained('vehicles')->cascadeOnDelete();
            $table->foreignId('blog_category_id')->nullable()->unique()->constrained('blog_categories')->cascadeOnDelete();
            $table->foreignId('blog_post_id')->nullable()->unique()->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('gallery_album_id')->nullable()->unique()->constrained('gallery_albums')->cascadeOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 2048)->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image_path')->nullable();
            $table->string('og_image_alt')->nullable();
            $table->timestamps();
        });

        if ($driver === 'sqlite') {
            // SQLite cannot add CHECK constraints after CREATE TABLE; equivalent triggers enforce both writes.
            DB::unprepared("CREATE TRIGGER seo_metadata_owner_insert BEFORE INSERT ON seo_metadata WHEN (NEW.page_id IS NOT NULL) + (NEW.tour_package_id IS NOT NULL) + (NEW.vehicle_category_id IS NOT NULL) + (NEW.vehicle_id IS NOT NULL) + (NEW.blog_category_id IS NOT NULL) + (NEW.blog_post_id IS NOT NULL) + (NEW.gallery_album_id IS NOT NULL) <> 1 BEGIN SELECT RAISE(ABORT, 'SEO metadata requires exactly one owner'); END");
            DB::unprepared("CREATE TRIGGER seo_metadata_owner_update BEFORE UPDATE ON seo_metadata WHEN (NEW.page_id IS NOT NULL) + (NEW.tour_package_id IS NOT NULL) + (NEW.vehicle_category_id IS NOT NULL) + (NEW.vehicle_id IS NOT NULL) + (NEW.blog_category_id IS NOT NULL) + (NEW.blog_post_id IS NOT NULL) + (NEW.gallery_album_id IS NOT NULL) <> 1 BEGIN SELECT RAISE(ABORT, 'SEO metadata requires exactly one owner'); END");
        } else {
            DB::statement('ALTER TABLE seo_metadata ADD CONSTRAINT seo_metadata_exactly_one_owner CHECK ((page_id IS NOT NULL) + (tour_package_id IS NOT NULL) + (vehicle_category_id IS NOT NULL) + (vehicle_id IS NOT NULL) + (blog_category_id IS NOT NULL) + (blog_post_id IS NOT NULL) + (gallery_album_id IS NOT NULL) = 1)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_metadata');
    }
};
