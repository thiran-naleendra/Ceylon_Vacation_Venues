<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'home' => ['Home', 'home', 'home', 0],
            'about' => ['About Us', 'about-us', 'standard', 1],
            'villas-houses' => ['Villas & Houses', 'villas-houses', 'listing', 2],
            'visa-extension' => ['Visa Assistance', 'visa-extension', 'service', 3],
            'baggage-transport' => ['Airport Baggage Recovery', 'baggage-transport', 'service', 4],
            'contact' => ['Contact Us', 'contact-us', 'contact', 5],
            'privacy-policy' => ['Privacy Policy', 'privacy-policy', 'legal', 6],
            'terms-and-conditions' => ['Terms & Conditions', 'terms-and-conditions', 'legal', 7],
        ];

        foreach ($pages as $key => [$title, $slug, $template, $sortOrder]) {
            $page = Page::query()->firstOrNew(['page_key' => $key]);
            if (! $page->exists) {
                $page->forceFill(['page_key' => $key, 'title' => $title, 'slug' => $slug, 'template' => $template]);
            }
            $page->forceFill(['sort_order' => $sortOrder])->save();
        }
    }
}
