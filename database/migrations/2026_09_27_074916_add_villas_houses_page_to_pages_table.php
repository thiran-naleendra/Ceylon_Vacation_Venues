<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('pages')->insertOrIgnore([
            'page_key' => 'villas-houses',
            'title' => 'Villas & Houses',
            'slug' => 'villas-houses',
            'summary' => 'Find a comfortable villa or house for your stay in Sri Lanka.',
            'body' => '<h2>Find your stay in Sri Lanka</h2><p>Explore our published villas and houses, then contact our team to check availability and arrange your stay.</p>',
            'template' => 'listing',
            'status' => 'published',
            'published_at' => now(),
            'sort_order' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->where('page_key', 'villas-houses')->delete();
    }
};
