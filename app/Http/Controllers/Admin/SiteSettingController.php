<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function __construct(private AdminActivityLogger $activityLogger)
    {
    }

    public function index(): View
    {
        return view('admin.settings', ['settings' => SiteSetting::current()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'announcement' => ['nullable', 'string', 'max:2000'],
            'announcement_enabled' => ['nullable', 'boolean'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'maintenance_message' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings = SiteSetting::current();
        $validated['announcement_enabled'] = $request->boolean('announcement_enabled');
        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');

        foreach (['logo', 'banner'] as $field) {
            if ($request->hasFile($field)) {
                if ($settings->{$field}) {
                    Storage::disk('public')->delete($settings->{$field});
                }

                $validated[$field] = $request->file($field)->store('site', 'public');
            }
        }

        $settings->update($validated);
        SiteSetting::forgetCurrent();
        $this->activityLogger->record($request->user(), 'site.settings_updated', 'Site ayarlari guncellendi.', $settings, [
            'maintenance_mode' => $settings->maintenance_mode,
            'announcement_enabled' => $settings->announcement_enabled,
        ]);

        return back()->with('success', 'Site ayarları kaydedildi.');
    }
}
