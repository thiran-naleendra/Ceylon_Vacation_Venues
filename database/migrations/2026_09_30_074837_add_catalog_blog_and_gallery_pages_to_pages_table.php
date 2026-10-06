<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('pages')->insertOrIgnore([
            ['page_key' => 'tour-packages', 'title' => 'Tour Packages', 'slug' => 'tour-packages', 'summary' => 'Explore curated Sri Lanka tour packages for memorable journeys across the island.', 'body' => null, 'template' => 'listing', 'status' => 'published', 'published_at' => $now, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'vehicle-rental', 'title' => 'Vehicle Rental', 'slug' => 'vehicle-rental', 'summary' => 'Find the right vehicle for a comfortable and flexible journey around Sri Lanka.', 'body' => null, 'template' => 'listing', 'status' => 'published', 'published_at' => $now, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'gallery', 'title' => 'Gallery', 'slug' => 'gallery', 'summary' => 'Explore moments from destinations and journeys across Sri Lanka.', 'body' => null, 'template' => 'listing', 'status' => 'published', 'published_at' => $now, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['page_key' => 'blog', 'title' => 'Blog', 'slug' => 'blog', 'summary' => 'Read destination ideas and practical guidance for planning your Sri Lankan journey.', 'body' => null, 'template' => 'listing', 'status' => 'published', 'published_at' => $now, 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->whereIn('page_key', ['tour-packages', 'vehicle-rental', 'gallery', 'blog'])->delete();
    }
};
