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
            'tour-packages' => ['Tour Packages', 'tour-packages', 'listing', 1],
            'villas-houses' => ['Villas & Houses', 'villas-houses', 'listing', 2],
            'vehicle-rental' => ['Vehicle Rental', 'vehicle-rental', 'listing', 3],
            'visa-extension' => ['Visa Assistance', 'visa-extension', 'service', 4],
            'baggage-transport' => ['Airport Baggage Recovery', 'baggage-transport', 'service', 5],
            'gallery' => ['Gallery', 'gallery', 'listing', 6],
            'blog' => ['Blog', 'blog', 'listing', 7],
            'about' => ['About Us', 'about-us', 'standard', 8],
            'contact' => ['Contact Us', 'contact-us', 'contact', 9],
            'privacy-policy' => ['Privacy Policy', 'privacy-policy', 'legal', 10],
            'terms-and-conditions' => ['Terms & Conditions', 'terms-and-conditions', 'legal', 11],
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
