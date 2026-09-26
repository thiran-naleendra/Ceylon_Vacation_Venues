<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingsRequest;
use App\Models\AuditLog;
use App\Models\SocialLink;
use App\Models\WebsiteSetting;
use App\Services\AllowedHtmlSanitizer;
use App\Services\BrandAssetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class SettingsController extends Controller
{
    public function __construct(private readonly AllowedHtmlSanitizer $sanitizer, private readonly BrandAssetService $assets) {}

    public function edit(): View
    {
        Gate::authorize('manage-settings');
        $settings = WebsiteSetting::query()->get()->pluck('value', 'key');
        $socialLinks = SocialLink::query()->ordered()->get();

        return view('admin.settings.edit', compact('settings', 'socialLinks'));
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $oldLogo = WebsiteSetting::query()->where('key', 'branding.logo_path')->first()?->value;
        $oldFavicon = WebsiteSetting::query()->where('key', 'branding.favicon_path')->first()?->value;
        $newLogo = null;
        $newFavicon = null;

        try {
            $newLogo = $request->hasFile('logo') ? $this->assets->store($request->file('logo'), 'logo') : null;
            $newFavicon = $request->hasFile('favicon') ? $this->assets->store($request->file('favicon'), 'favicon') : null;

            DB::transaction(function () use ($request, $data, $oldLogo, $oldFavicon, $newLogo, $newFavicon): void {
                $values = [
                    'business.name' => ['business', $data['business_name']], 'business.legal_name' => ['business', $data['legal_name'] ?? null],
                    'business.registration_number' => ['business', $data['registration_number'] ?? null],
                    'contact.phone' => ['contact', $data['phone'] ?? null], 'contact.whatsapp_number' => ['contact', $data['whatsapp_number'] ?? null],
                    'contact.email' => ['contact', $data['email'] ?? null], 'contact.notification_email' => ['contact', $data['notification_email'] ?? null],
                    'contact.address' => ['contact', $data['address'] ?? null], 'contact.map_embed_url' => ['contact', $data['map_embed_url'] ?? null], 'footer.content' => ['footer', $this->sanitizer->sanitize($data['footer_content'] ?? null)],
                    'branding.logo_path' => ['branding', $newLogo ?? ($request->boolean('remove_logo') ? null : $oldLogo)],
                    'branding.favicon_path' => ['branding', $newFavicon ?? ($request->boolean('remove_favicon') ? null : $oldFavicon)],
                ];
                foreach ($values as $key => [$group, $value]) {
                    $setting = WebsiteSetting::query()->where('key', $key)->first() ?? new WebsiteSetting;
                    $setting->forceFill(['key' => $key, 'group' => $group, 'value' => $value])->save();
                }

                SocialLink::query()->delete();
                foreach ($data['social_links'] ?? [] as $link) {
                    SocialLink::query()->create($link);
                }
                (new AuditLog)->forceFill(['actor_id' => auth()->id(), 'action' => 'settings.updated', 'subject_type' => WebsiteSetting::class, 'subject_id' => 0, 'subject_label' => 'Website settings', 'request_id' => (string) Str::uuid()])->save();
            });
        } catch (Throwable $exception) {
            $this->assets->delete($newLogo);
            $this->assets->delete($newFavicon);
            throw $exception;
        }

        if ($newLogo || $request->boolean('remove_logo')) {
            $this->assets->delete(is_string($oldLogo) ? $oldLogo : null);
        }
        if ($newFavicon || $request->boolean('remove_favicon')) {
            $this->assets->delete(is_string($oldFavicon) ? $oldFavicon : null);
        }

        return back()->with('success', 'Website settings updated.');
    }
}
