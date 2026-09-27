<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMediaServingTest extends TestCase
{
    public function test_public_media_is_served_when_the_storage_symlink_is_unavailable(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('pages/about/hero.webp', 'image-content');

        $this->assertTrue(Route::has('storage.public'));
        $this->assertFalse(Route::has('storage.local'));
        $this->get('/storage/pages/about/hero.webp')
            ->assertOk();
    }
}
