<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'home' => ['Home', 'home', 'home'],
            'about' => ['About Us', 'about-us', 'standard'],
            'visa-extension' => ['Visa Extension', 'visa-extension', 'service'],
            'baggage-transport' => ['Baggage Transport', 'baggage-transport', 'service'],
            'contact' => ['Contact Us', 'contact-us', 'contact'],
            'privacy-policy' => ['Privacy Policy', 'privacy-policy', 'legal'],
            'terms-and-conditions' => ['Terms & Conditions', 'terms-and-conditions', 'legal'],
        ];

        foreach ($pages as $key => [$title, $slug, $template]) {
            $page = Page::query()->firstOrNew(['page_key' => $key]);
            if (! $page->exists) {
                $page->forceFill(['page_key' => $key, 'title' => $title, 'slug' => $slug, 'template' => $template])->save();
            }
        }
    }
}
